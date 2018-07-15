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
    JsonSerializable,
    stdClass,
    Throwable,
    TypeError;

use at\util\ {
  DateTime,
  Json,
  ValueException
};

/**
 * general value/variable handling utilities and filtering tools.
 */
class Value {

  /**
   * php data types and psuedotypes.
   *
   * @type string ARRAY     array type
   * @type string BOOL      boolean type
   * @type string CALLABLE  callable psuedotype
   * @type string DATETIME  DateTimeInterface, unix timestamps, or $time strings
   * @type string FLOAT     float type
   * @type string INT       integer type
   * @type string ITERABLE  arrays or Traversable objects
   * @type string JSONABLE  JsonSerializable or stdClass objects; any other non-resource
   * @type string NULL      null type
   * @type string OBJECT    object type
   * @type string RESOURCE  resource type
   * @type string STRING    string type
   */
  const ARRAY = 'array';
  const BOOL = 'boolean';
  const CALLABLE = 'callable';
  const DATETIME = 'datetimeable';
  const FLOAT = 'float';
  const INT = 'integer';
  const ITERABLE = 'iterable';
  const JSONABLE = 'jsonable';
  const NULL = 'null';
  const OBJECT = 'object';
  const RESOURCE = 'resource';
  const STRING = 'string';

  /** @type array  known alias => datatype map. */
  const TYPE_TR = ['double' => self::INT, 'NULL' => self::NULL];

  /**
   * filter() option keys/values.
   *
   * @type string FILTER        filter option key: filter
   * @type string OPTS          filter option key: filter_var options
   * @type string FLAGS         filter option key: filter_var flags
   *
   * @type string OPT_DEFAULT   filter option key: default value
   * @type string OPT_MAX       filter option key: maximum value / length
   * @type string OPT_MIN       filter option key: minimum value / length
   * @type string OPT_NO_EMPTY  filter option key: treat empty values as failure
   * @type string OPT_REGEX     filter option key: regular expression
   * @type string OPT_THROW     filter option key: throw on failure?
   *
   * @type string OPT_MISSING   filter option key: handling for missing keys when mapping
   * @type int    ADD_MISSING   OPT_MISSING value: add keys from filter that are not in values
   * @type int    OMIT_MISSING  OPT_MISSING value: omit keys from values that are not in filters
   */
  const FILTER = 'filter';
  const FLAGS = 'flags';
  const OPTS = 'options';

  const OPT_DEFAULT = 'default';
  const OPT_MAX = 'max_range';
  const OPT_MIN = 'min_range';
  const OPT_NO_EMPTY = 'no_empty';
  const OPT_REGEX = 'regex';
  const OPT_THROW = 'throw';

  const OPT_MISSING = 'missing';
  const ADD_MISSING = 1;
  const OMIT_MISSING = (1<<1);

  /** @type mixed[]  filter() aliases for FILTER_* constants. */
  const FILTER_ALIASES = [
    self::BOOL => FILTER_VALIDATE_BOOLEAN,
    self::DATETIME => [self::class, '_filterDatetime'],
    self::FLOAT => FILTER_VALIDATE_FLOAT,
    self::INT => FILTER_VALIDATE_INT,
    self::JSONABLE => [self::class, '_filterJsonable'],
    self::STRING => [self::class, '_filterString']
  ];


  /**
   * captures var_dump output and returns it as a string.
   * @see <http://php.net/var_dump>
   *
   * @return string  debugging information about the expression(s)
   */
  public static function debug(...$expressions) {
    if (empty($expressions)) {
      throw new ValueException(ValueException::NO_EXPRESSIONS);
    }
    ob_start();
    var_dump(...$expressions);
    return ob_get_clean();
  }

  /**
   * filters a value as the given type.
   *
   * @param mixed  $val      the value to filter
   * @param mixed  $filter   a Value::{TYPE} constant, FILTER_* constant, or callable
   * @param array  $options  map of filter options
   * @throws ValueException  if filter is invalid, or on failure when OPT_THROW is true
   * @return mixed|null      the filtered value on success; null otherwise
   */
  public static function filter($val, $filter, array $options = []) {
    // check for $filter aliases
    $filter = self::FILTER_ALIASES[$filter] ?? $filter;

    // apply filter
    if (is_int($filter)) {
      $opts = [
        self::FLAGS => ($options[self::FLAGS] ?? 0) | FILTER_NULL_ON_FAILURE,
        self::OPTS => $options[self::OPTS] ?? $options
      ];
      $value = filter_var($val, $filter, $opts);
    } elseif (is_callable($filter)) {
      $value = $filter($val, $options);
    } elseif (is_string($filter)) {
      // @todo regex filter?
      if (! self::is($value, $filter)) {
        $value = null;
      }
    } else {
      $filter = array_search($filter, self::FILTER_ALIASES) ?: $filter;
      throw new ValueException(ValueException::INVALID_FILTER, ['filter' => $filter]);
    }

    // apply post-filter options
    if (empty($value) && ($options[self::OPT_NO_EMPTY] ?? false)) {
      $value = null;
    }
    if ($value === null && ($options[self::OPT_THROW] ?? false)) {
      throw new ValueException(
        ValueException::FILTER_FAILURE,
        ['filter' => $filter, 'value' => $val]
      );
    }

    return $value;
  }

  /**
   * applies a filter to multiple values.
   *
   * @param mixed[] $vals     array of values to filter
   * @param mixed   $filter   a Value::{TYPE} constant, FILTER_* constant, or callable
   * @param array   $options  map of filter options
   * @throws ValueException   if filter is invalid, or on failure when OPT_THROW is true
   * @return array            the filtered values on success
   */
  public static function filterEach(array $vals, $filter, array $options = []) : array {
    $filtered = [];
    foreach ($vals as $val) {
      $filtered = self::filter($val, $filter, $options);
    }

    return ($options[self::OPT_NO_EMPTY] ?? false) ?
      array_filter($filtered) :
      $filtered;
  }

  /**
   * maps multiple filters to multiple values.
   *
   * filters may be defined as Value::{TYPE} constants, FILTER_* constants, or callables.
   * alternatively, they may be defined as [self::FILTER, self::OPTS] arrays.
   *
   * values are assigned to filters by key.
   * by default, values without matching filters are returned unfiltered,
   * and filters without matching values are ignored.
   * this behavior can be changed with the OPT_MISSING option.
   *
   * @param mixed[] $vals     array of values to filter
   * @param mixed[] $filters  array of filter definitions
   * @param array   $options  map of filter options
   * @throws ValueException   if filter is invalid, or on failure when OPT_THROW is true
   * @return array            the filtered values on success
   */
  public static function filterMap(array $vals, array $filters, array $options = []) : array {
    // add/omit keys
    $keys = array_keys($vals);
    $filterKeys = array_keys($filters);
    $missing = $options[self::OPT_MISSING] ?? 0;
    if ($missing && self::ADD_MISSING === self::ADD_MISSING) {
      $keys = array_merge($keys, $filterKeys);
    }
    if ($missing && self::OMIT_MISSING === self::OMIT_MISSING) {
      $keys = array_intersect($keys, $filterKeys);
    }

    $filtered = [];
    foreach ($keys as $key) {
      $filtered[$key] = self::filter(
        $vals[$key] ?? null,
        $filters[$key][self::FILTER] ?? $filters[$key] ?? FILTER_DEFAULT,
        ($filters[$key][self::OPTS] ?? []) + $options
      );
    }
    return ($options[self::OPT_NO_EMPTY] ?? false) ?
      array_filter($filtered) :
      $filtered;
  }

  /**
   * checks a value's type against one or more given types/pseudotypes/classnames.
   *
   * to specify types/psuedotypes, use the appropriate Value constant.
   * to specify classnames, use the ::class magic constant.
   *
   * @param mixed  $val     the value to test
   * @param string …$types  list of types/classnames to check against
   * @return bool           true if value matches at least one of given types; false otherwise
   */
  public static function is($val, string ...$types) : bool {
    $valtype = self::type($val);

    foreach ($types as $type) {
      if (
        ($valtype === $type) ||
        ($val instanceof $type) ||
        (($type === self::CALLABLE) && is_callable($val)) ||
        (($type === self::ITERABLE) && is_iterable($val)) ||
        (($type === self::JSONABLE) && self::_isJsonable($val)) ||
        (($type === self::DATETIME) && self::_isDateTimeable($val))
      ){
        return true;
      }
    }
    return false;
  }

  /**
   * like is(), but throws a TypeError on failure.
   *
   * note, the stack trace will start from here:
   * look at the next line to see where it was actually triggered.
   *
   * @param string  $name    name of given argument (used in Error message)
   * @param mixed   $val     the argument to test
   * @param string  …$types  list of types/classnames to check against
   * @throws TypeError       if argument fails type check
   */
  public static function hint(string $name, $val, string ...$types) {
    if (! self::is($val, ...$types)) {
      $l = implode('|', $types);
      $t = self::type($val);
      $m = "{$name} must be" . (count($types) > 1 ? ' one of ' : ' ') . "{$l}; {$t} provided";
      throw new TypeError($m, E_WARNING);
    }
  }

  /**
   * gets a value's type, or classname if an object.
   *
   * @param mixed $val  the value to check
   * @return string     the value's type or classname
   */
  public static function type($val): string {
    return is_object($val) ?
      get_class($val) :
      strtr(gettype($val), self::TYPE_TR);
  }


  /**
   * filters a value as a datetime value.
   *
   * accepts instances of DateTimeInterface, unix timestamps (integers/floats),
   * and strings which strtotime() understands.
   *
   * @param mixed $val               the value to filter
   * @param array $options           filter options
   * @return DateTimeInterface|null  a DateTime instance on success; null otherwise
   */
  protected static function _filterDateTime($val, array $options = []) : ? DateTimeInterface {
    if ($val instanceof DateTimeInterface) {
      return $val;
    }

    try {
      return self::filter($val, Value::FLOAT) ?
        DateTime::createFromUnixtime($val) :
        DateTime::create($val);
    } catch (Throwable $e) {
      // not datetimeable
      return null;
    }
  }

  /**
   * filters a value as json-encodable.
   *
   * accepts any value type except resource;
   * with the additional restriction that objects must be stdClass or JSONSerializable.
   *
   * @param mixed $val      the value to filter
   * @param array $options  filter options
   * @return string|null    json-encoded value on success; null otherwise
   */
  protected static function _filterJsonable($val, array $options = []) : ? string {
    if (
      is_resource($val) || (
        is_object($val) &&
        ! ($val instanceof stdClass || $val instanceof JsonSerializable)
      )
    ) {
      return null;
    }

      return Json::encode($val);
    try {
      return Json::encode($val);
    } catch (Throwable $e) {
      // not jsonable
      return null;
    }
  }

  /**
   * filters a value as being string-representable.
   *
   * accepts strings, ints, floats, and objects with a __toString() method.
   *
   * @param mixed $val      the value to filter
   * @param array $options  filter options
   * @return string|null    stringified value on success; null otherwise
   */
  public static function _filterString($val, array $options = []) : ? string {
    if (
      self::is($val, Value::STRING, Value::FLOAT, Value::INT) ||
      (is_object($val) && method_exists($val, '__toString'))
    ) {
      return (string) $val;
    }

    return null;
  }

  /**
   * checks whether a value is a datetime value.
   * @see self::_filterDateTime()
   *
   * @param mixed $val  the value to check
   * @return bool       true if value is a datetime value; false otherwise
   */
  protected static function _isDateTimeable($val) : bool {
    return self::_filterDateTime($val) !== null;
  }

  /**
   * checks whether a value can be serialized as json.
   * @see self::_filterJsonable()
   *
   * @param mixed $val  the value to check
   * @return bool       true if value is jsonable; false otherwise
   */
  protected static function _isJsonable($val) : bool {
    return self::_filterJsonable($val) !== null;
  }
}
