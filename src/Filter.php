<?php
/**
 * @package    at.util
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GPL-3.0 (only)
 *
 *  This program is free software: you can redistribute it and/or modify it
 *  under the terms of the GNU General Public License, version 3.
 *  The right to apply the terms of later versions of the GPL is RESERVED.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program.
 *  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 */
declare(strict_types = 1);

namespace at\util;

use DateTimeInterface,
    Throwable,
    Traversable;

use at\exceptable\Handler;

use at\util\ {
  DateTime,
  FilterException
};

/**
 * wraps filter functions with a streamlined api and better defaults.
 */
class Filter {

  /**
   * @type int COERCE_ARRAY   filter flag: coerce filter value to array.
   * @type int REQUIRE_ARRAY  filter flag: require filter value to be an array.
   */
  const COERCE_ARRAY = FILTER_FORCE_ARRAY;
  const REQUIRE_ARRAY = FILTER_REQUIRE_ARRAY;

  /**
   * @type string OPT_ADD_MISSING   filter option key: add keys not present in values?
   * @type string OPT_DEFAULT       filter option key: default value.
   * @type string OPT_MIN           filter option key: minimum integer value.
   * @type string OPT_MAX           filter option key: maximum integer value.
   * @type string OPT_OMIT_EMPTY    filter option key: remove empty values from filtered arrays?
   * @type string OPT_OMIT_MISSING  filter option key: omit keys not present in filters?
   * @type string OPT_OMIT_NULL     filter option key: remove null values from filtered arrays?
   * @type string OPT_REGEX         filter option key: regular expression.
   * @type string OPT_THROW         filter_option_key: throw on failure.
   */
  const OPT_ADD_MISSING = 'add_missing';
  const OPT_DEFAULT = 'default';
  const OPT_MIN = 'min_range';
  const OPT_MAX = 'max_range';
  const OPT_OMIT_EMPTY = 'omit_empty';
  const OPT_OMIT_MISSING = 'omit_missing';
  const OPT_OMIT_NULL = 'omit_null';
  const OPT_REGEX = 'regex';
  const OPT_THROW = 'throw';

  /**
   * filter shorthands as aliases or [filter, base flags, base options] tuples.
   *
   * @type array BOOL      aliases FILTER_VALIDATE_BOOLEAN.
   * @type array DATETIME  validates value as date/time and returns DateTime on success.
   * @type array FLOAT     aliases FILTER_VALIDATE_FLOAT
   * @type array INT       aliases FILTER_VALIDATE_INT
   * @type array SERIAL    validates value as non-negative integer; converts to int on success.
   * @type array STRING    validates value as string-able; converts to string on success.
   */
  const BOOL = FILTER_VALIDATE_BOOLEAN;
  const DATETIME = [__CLASS_, 'validateDateTime'];
  const FLOAT = FILTER_VALIDATE_FLOAT;
  const INT = FILTER_VALIDATE_INT;
  const SERIAL = [FILTER_VALIDATE_INT, 0, [self::OPT_MIN => 0]];
  const STRING = [__CLASS__, 'validateString'];


  /**
   * applies a filter to a value.
   *
   * accepts:
   * - built-in php filters: @see http://php.net/filter_var
   * - callables: will use FILTER_CALLBACK
   * - Filter::{alias} constants: will filter for given type
   * - regular expressions or PRO instances: will use FILTER_VALIDATE_REGEX
   * - Vars::{(pseudo)type} constants: will filter for given type
   *    (note; strict type comparison and no casting)
   * - fully qualified classnames: will filter for given class
   *
   * will require a scalar value unless COERCE|REQUIRE_ARRAY flags are set.
   *
   * @param mixed $value    the value to filter
   * @param mixed $filter   the filter to apply
   * @param int   $flags    filter flags
   * @param array $options  filter options
   * @throws VarsException  if filter definition is invalid, or if a callback throws
   * @return mixed|null     the filtered value on success; or null on failure
   */
  public static function apply($value, $filter, int $flags = 0, array $options = []) {
    // prep args
    list($filter, $flags, $options) = self::_prep($filter, $flags, $options);

    // pre-filter for array vs. scalar constraints
    if (($flags & self::REQUIRE_ARRAY === self::REQUIRE_ARRAY) && ! is_array($value)) {
      return is_array($options[self::OPT_DEFAULT] ?? null) ?
        $options[self::OPT_DEFAULT] :
        null;
    }
    if (($flags & self::FORCE_ARRAY === self::FORCE_ARRAY) && ! is_array($value)) {
      $value = ($value === null) ? [] : [$value];
    } else {
      $flags |= self::REQUIRE_SCALAR;
    }

    // run filter
    try {
      $errorExceptions = (new Handler)->throw(E_ALL)->register();
      $filtered = filter_var($value, $filter, ['flags' => $flags, 'options' => $options]);
    } catch (\Throwable $e) {
      throw new FilterException(FilterException::BAD_CALL_RIPLEY, $e);
    } finally {
      $errorExceptions->unregister();
    }

    // apply post-filter options
    if (is_array($filtered)) {
      $n = array_search(null, $filtered);
      if ($n !== false && ($options[self::OPT_THROW] ?? false)) {
        throw new FilterException(
          FilterException::INVALID_FILTER,
          ['filter' => $filter, 'value' => $value[$n]]
        );
      }
      if ($options[self::OPT_OMIT_NULL] ?? false) {
        return array_filter($filtered, function ($value) { return $value !== null; });
      }
      if ($options[self::OPT_OMIT_EMPTY] ?? false) {
        return array_filter($filtered);
      }
      return $filtered;
    }

    if ($filtered === null && ($options[self::OPT_THROW] ?? false)) {
      throw new FilterException(
        FilterException::INVALID_FILTER,
        ['filter' => $filter, 'value' => $value]
      );
    }
    return $filtered;
  }

  /**
   * applies a map of filters to multiple values.
   *
   * @param array $values       key:value pairs to filter
   * @param array $filter       key:filter pairs to apply
   * @param int   $baseFlags    base filter flags (applied to all filters)
   * @param array $baseOptions  base filter options (applied to all filters)
   * @return array              the filtered values
   */
  public static function map(
    array $values,
    array $filters,
    int $baseFlags = 0,
    array $baseOptions = []
  ) : array {
    $keys = array_keys($values);
    if ($baseOptions[self::OPT_ADD_MISSING] ?? false) {
      $keys = array_merge($keys, array_keys($filters));
    }
    if ($baseOptions[self::OPT_OMIT_MISSING] ?? false) {
      $keys = array_intersect($keys, array_keys($filters));
    }

    $filtered = [];
    foreach ($keys as $key) {
      $value = $values[$key] ?? null;

      $filter = $filter[$key]['filter'] ?? $filter[$key] ?? FILTER_DEFAULT;
      $flags = is_int($filter[$key]['flags'] ?? null) ?
        $filter[$key]['flags'] | $baseFlags :
        $baseFlags;
      $options = is_array($filter[$key]['options'] ?? null) ?
        $filter[$key]['options'] + $baseOptions :
        $baseOptions;

      $applied = self::apply($value, $filter, $flags, $options);

      if ($applied === null) {
        if ($options[self::THROW] ?? false) {
          throw new FilterException(FilterException::FILTER_FAILURE, ['definition' => $filter]);
        }
        if ($options[self::OPT_OMIT_NULL] ?? false) {
          continue;
        }
      }
      if (empty($applied) && ($options[self::OPT_OMIT_EMPTY] ?? false)) {
        continue;
      }

      $filtered[$key] = $applied;
    }
    return $filtered;
  }

  /**
   * applies a filter to multiple values.
   *
   * @param mixed $values   values to filter (scalar values will be coerced to array)
   * @param mixed $filter   filter to apply
   * @param int   $flags    filter flags
   * @param array $options  filter options
   * @return array          the filtered values
   */
  public static function walk($values, $filter, int $flags = 0, array $options = []) : array {
    if (! is_array($values)) {
      $values = ($values instanceof Traversable) ?
        iterator_to_array($values) :
        ($values === null ? [] : [$values]);
    }
    $flags |= self::REQUIRE_ARRAY;

    return self::apply($value, $filter, $flags, $options);
  }

  /**
   * resolves filter aliases, applies default flags and options.
   *
   * @param mixed $filter   filter
   * @param int   $flags    filter flags
   * @param array $options  filter options
   * @return array          [filter, flags, options] tuple
   */
  protected static function _prep($filter, int $flags, array $options) : array {
    // always null on failure
    $flags |= FILTER_NULL_ON_FAILURE;

    // filter shorthands
    if (is_callable($filter)) {
      // callback function
      $options = $filter;
      $filter = FILTER_CALLBACK;
    } elseif (is_array($filter) && isset($filter[0], $filter[1], $filter[2])) {
      // [filter, flags, options] tuple
      list($filter, $baseFlags, $baseOpts) = $filter;
      $flags |= $baseFlags;
      $options += $baseOpts;
    } elseif (Vars::isRegex($filter)) {
      // regular expression (might be PRO instance)
      $options[self::OPT_REGEX] = (string) $filter;
      $filter = FILTER_VALIDATE_REGEXP;
    } elseif (is_string($filter)) {
      // type/pseudotype/classname
      $options = function ($value) use ($filter) {
        return self::_validateType($value, $filter);
      };
      $filter = FILTER_CALLBACK;
    }

    if (! is_int($filter)) {
      throw new FilterException(FilterException::INVALID_FILTER, ['definition' => $filter]);
    }

    return [$filter, $flags, $options];
  }

  //protected static function _validateArray($value) {}

  /**
   * validates value as date/time and returns DateTime on success.
   *
   * @param mixed $value    the value to filter
   * @return DateTime|null  a DateTime instance on success; null otherwise
   */
  protected static function _validateDateTime($value) {
    try {
      return ($datetimeable instanceof DateTimeInterface) ?
        $datetimeable :
        new DateTime($datetimeable);
    } catch (Throwable $e) {
      return null;
    }
  }

  //protected static function _validateRegex($value) {}

  /**
   * validates stringable values and converts to string on success.
   *
   * scalars and objects with __toString() methods are "stringable."
   * note that booleans are returned as "true" and "false".
   *
   * @param mixed $stringable  a stringable value
   * @throws VarsException     if value cannot be cast to string
   * @return string            the value as a string
   */
  protected static function _validateString($value) {
    switch (Vars::type($stringable)) {
      case Vars::OBJECT :
        if (! method_exists($stringable, '__toString')) {
          break;
        }
      case Vars::FLOAT :
      case Vars::INT :
      case Vars::STRING :
        return (string) $stringable;
      case Vars::BOOL :
      case Vars::NULL :
        return json_encode($stringable);
      default :
        return null;
    }
  }

  protected static function _validateType($value, string $type) {}
}
