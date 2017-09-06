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
 *
 * like the native filter_* functions, values are treated as [arrays of] strings.
 * passing objects will NOT work as expected.
 */
class Filter {

  /** @type string OPT_DEFAULT  filter option key: default value. */
  const OPT_DEFAULT = 'default';

  /**
   * flags and options for Filter::INT.
   *
   * @type int    HEX      filter flag: permits integers in hex format.
   * @type int    OCTAL    filter flag: permits integers in octal format.
   * @type string OPT_MIN  filter option key: minimum integer value.
   * @type string OPT_MAX  filter option key: maximum integer value.
   */
  const HEX = FILTER_FLAG_ALLOW_HEX;
  const OCTAL = FILTER_FLAG_ALLOW_OCTAL;
  const OPT_MIN = 'min_range';
  const OPT_MAX = 'max_range';

  /**
   * flags for array behavior.
   *
   * @type int COERCE_ARRAY   filter flag: coerce filter value to array.
   * @type int REQUIRE_ARRAY  filter flag: require filter value to be an array.
   */
  const COERCE_ARRAY = FILTER_FORCE_ARRAY;
  const REQUIRE_ARRAY = FILTER_REQUIRE_ARRAY;

  /**
   * flags for filtering results/resultsets.
   *
   * @type int    NULL_EMPTY_RESULT  convert empty values to null.
   * @type int    THROW_ON_FAILURE   throw if a null value is encountered.
   * @type int    OMIT_ON_FAILURE    omit null values from results (applies only to arrays).
   */
  const NULL_EMPTY_RESULT = 1;
  const THROW_ON_FAILURE = (1<<1);
  const OMIT_ON_FAILURE = (1<<2);

  /**
   * options for handling missing values in Filter::map().
   *
   * @type string OPT_MISSING   filter option key: handling for missing keys when mapping.
   * @type int    ADD_MISSING   add keys present in filter but missing from values.
   * @type int    OMIT_MISSING  omit keys present in values but missing from filters.
   */
  const OPT_MISSING = 'missing';
  const ADD_MISSING = 1;
  const OMIT_MISSING = (1<<1);

  /** @type string OPT_REGEX  filter option key: regular expression. */
  const OPT_REGEX = 'regex';

  /**
   * array keys for filter_var() $flags.
   *
   * @type string FLAGS  filter flags
   * @type string OPTS   filter options
   */
  const FLAGS = 'flags';
  const OPTS = 'options';

  /**
   * filter shorthands as aliases or [filter, base flags, base options] tuples.
   *
   * @type int      BOOL      aliases FILTER_VALIDATE_BOOLEAN.
   * @type callable DATETIME  validates value as date/time and returns DateTime on success.
   * @type callable EMAIL     uses FILTER_VALIDATE_EMAIL, but allows for IDNs.
   * @type int      FLOAT     aliases FILTER_VALIDATE_FLOAT
   * @type int      INT       aliases FILTER_VALIDATE_INT
   * @type int      IP        aliases FILTER_VALIDATE_IP
   * @type array    SERIAL    validates value as non-negative integer; converts to int on success.
   * @type callable STRING    validates value as string-able; converts to string on success.
   */
  const BOOL = FILTER_VALIDATE_BOOLEAN;
  const DATETIME = [__CLASS__, '_validateDateTime'];
  const EMAIL = [__CLASS__, '_validateEmail'];
  const FLOAT = FILTER_VALIDATE_FLOAT;
  const INT = FILTER_VALIDATE_INT;
  const IP = FILTER_VALIDATE_IP;
  const SERIAL = [FILTER_VALIDATE_INT, 0, [self::OPT_MIN => 0]];
  const STRING = [__CLASS__, '_validateString'];


  /**
   * maps multiple values to multiple filters.
   *
   * the default behavior is to return values without a matching filter as-is.
   * this can be changed using the OPT_MISSING option:
   *  - ADD_MISSING adds (null) values where a filter has no matching value
   *  - OMIT_MISSING excludes values that do not have matching filters
   *
   * @param mixed $values     map of values to filter
   * @param mixed $filter     map of filters to apply (@see Filter::value() $filter)
   * @param int   $baseFlags  bitmask of base filter flags (applied to all filters)
   * @param array $baseOpts   map of base filter options (applied to all filters)
   * @throws FilterException  if any filter is invalid
   * @return array            the filtered values
   */
  public static function map(
    array $values,
    array $filters,
    int $baseFlags = 0,
    array $baseOptions = []
  ) : array {
    $keys = self::_prepMapKeys(
      array_keys($values),
      array_keys($filters),
      $baseOptions[self::OPT_MISSING] ?? 0
    );

    $filtered = [];
    foreach ($keys as $key) {
      $options = $filters[$key][self::OPTIONS] ?? [];
      if (is_array($options)) {
        $options += $baseOptions;
      }
      $flags = ($filters[$key][self::FLAGS] ?? 0) | $baseFlags;
      $filter = $filters[$key][self::FILTER] ?? $filters[$key] ?? FILTER_DEFAULT;

      $filtered[$key] = self::value($values[$key] ?? null, $filter, $flags, $options);
    }
    return $filtered;
  }

  /**
   * applies additional filtering to a filtered result(set).
   *
   * @param mixed $results  results returned from a filter method
   * @param int   $flags    bitmask of *_RESULT|*_ON_FAILURE flags
   * @return mixed          the post-filtered result
   */
  public static function result($results, int $flags) {
    $array = is_array($results);

    if (Vars::flags(self::NULL_EMPTY_RESULT, $flags)) {
      if ($array) {
        $results = array_map(function ($v) { return empty($v) ? null : $v; }, $results);
      } elseif (empty($results)) {
        $results = null;
      }
    }

    if (
      Vars::flag(self::THROW_ON_FAILURE, $failure) &&
      ($results === null || $array && in_array(null, $results))
    ) {
      throw new FilterException(FilterException::FILTER_FAILURE);
    }

    return (Vars::flag(self::OMIT_ON_FAILURE, $failure) && $array) ?
      array_filter($results, function ($v) { return $v !== null; }) :
      $results;
  }

  /**
   * applies a filter to a value.
   *
   * filters may be provided as one of:
   *  - one of the FILTER_VALIDATE_* or Filter::* shorthand constants.
   *  - a callback function for FILTER_CALLBACK.
   *  - a regular expression or PRO instance for FILTER_VALIDATE_REGEX.
   *
   * @param mixed $value      value to filter
   * @param mixed $filter     filter to apply
   * @param int   $flags      bitmask of filter flags
   * @param array $opts       map of filter options
   * @throws FilterException  if filter is invalid
   * @return mixed            the filtered value
   */
  public static function value($value, $filter, int $flags = 0, array $opts = []) {
    try {
      list($filter, $filterFlags) = self::_prepFilter($filter, $flags, $opts);
      return (new Handler)
        ->throw(E_ALL)
        ->during('filter_var', $value, $filter, $filterFlags);
    } catch (FilterException $e) {
      throw $e;
    } catch (TypeError $e) {
      throw new FilterException(FilterException::INVALID_FILTER, $e, ['filter' => $filter]);
    } catch (Throwable $e) {
      throw new FilterException(FilterException::BAD_CALL_RIPLEY, $e);
    }
  }

  /**
   * applies a filter to each of a collection of values.
   *
   * @param array $values     list of values to filter
   * @param mixed $filter     filter to apply (@see Filter::value() $filter)
   * @param int   $flags      bitmask of filter flags
   * @param array $opts       map of filter options
   * @throws FilterException  if filter is invalid
   * @return array            the filtered values
   */
  public static function walk(array $values, $filter, int $flags = 0, array $opts = []) : array {
    $filtered = [];
    foreach ($values as $value) {
      $filtered[] = self::value($value, $filter, $flags, $opts);
    }
    return $filtered;
  }


  /**
   * parses list of keys to include in mapping values to filters.
   *
   * @param array $values   value keys
   * @param array $filters  filter keys
   * @param int   $missing  mask of *_MISSING constants
   * @return array          list of keys
   */
  protected static function _mapKeys(array $values, array $filters, int $missing) : array {
    if (Vars::flag(self::ADD_MISSING, $missing)) {
      $values = array_merge($values, $filters);
    }
    if (Vars::flag(self::OMIT_MISSING, $missing)) {
      $values = array_intersect($values, $filters);
    }
    return $values;
  }

  /**
   * parses and preps filter arguments.
   *
   * @param mixed $filter    filter definition
   * @param int   $flags     filter flags
   * @param array $opts      filter options
   * @throw FilterException  if filter definition is invalid
   * @return array           {
   *    @type int            $0  FILTER_* constant
   *    @type int            $1  bitmask of filter flags
   *    @type array|callable $2  map of filter options, or callback for FILTER_CALLBACK
   *  }
   */
  protected static function _prepFilter($filter, int $flags, array $opts) : array {
    // callback function
    if (is_callable($filter)) {
      return [FILTER_CALLBACK, [self::FLAGS => $flags, self::OPTS => $filter]];
    }

    // [filter, flags, options] tuple
    if (is_array($filter)) {
      list($filter, $baseFlags, $baseOpts) = $filter + [FILTER_DEFAULT, 0, []];
      return [
        $filter,
        [self::FLAGS => $flags | $baseFlags, self::OPTS => $opts + $baseOpts]
      ];
    }

    // regular expression (might be PRO instance; stringify)
    if (Vars::isRegex($filter)) {
      return [
        FILTER_VALIDATE_REGEX,
        [self::FLAGS => $flags, self::OPTS => [[self::REGEX => (string) $filter] + $opts]]
      ];
    }

    // filter literal
    return [$filter, [self::FLAGS => $flags, self::OPTS => $opts]];
  }


  /**
   * validates datetimeable values and converts to DateTime on success.
   *
   * this method is public only so filter_var may invoke it as a callback.
   * it is not part of the public Filter API.
   *
   * @param mixed $value  the value to filter
   * @return mixed|null   the filtered value on success; null otherwise
   */
  public static function _validateDateTime($value) {
    try {
      return new DateTime($value);
    } catch (Throwable $e) {
      return null;
    }
  }

  /**
   * validates stringable values and converts to string on success.
   *
   * strings, ints, floats, bools, and objects with __toString() methods are "stringable."
   * note that booleans are returned as "true" and "false".
   *
   * this method is public only so filter_var may invoke it as a callback.
   * it is not part of the public Filter API.
   *
   * @param mixed $value  the value to filter
   * @return mixed|null   the filtered value on success; null otherwise
   */
  public static function _validateString($value) {
    switch (Vars::type($value)) {
      case Vars::OBJECT :
        return method_exists($value, '__toString') ? $value->__toString() : null;
      case Vars::FLOAT :
      case Vars::INT :
      case Vars::STRING :
        return (string) $value;
      case Vars::BOOL :
        return $value ? 'true' : 'false';
      default :
        return null;
    }
  }
}
