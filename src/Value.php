<?php
/**
 * @package    at.util
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2018
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

use Countable,
  DateTime,
  DateTimeInterface,
  JsonSerializable,
  stdClass,
  Throwable,
  TypeError;

use at\util\ {
  Json,
  ValueException
};

/**
 * General value/variable handling and introspection utilities.
 */
class Value {

  /**
   * PHP data types and psuedotypes.
   *
   * @type string ARRAY         arrays
   * @type string BOOL          booleans
   * @type string CALLABLE      anything that is_callable()
   * @type string COUNTABLE     anything that is_countable()
   * @type string DATETIMEABLE  DateTimeInterface, strings DateTime understands as $time
   * @type string FLOAT         floats
   * @type string INT           integers
   * @type string ITERABLE      anything that is_iterable()
   * @type string JSONABLE      stdClass|JsonSerializable objects, any other non-resource
   * @type string NULL          null
   * @type string OBJECT        objects of any class
   * @type string RESOURCE      open resources
   * @type string SCALAR        booleans, integers, floats, or strings
   * @type string STRING        strings
   */
  public const ARRAY = 'array';
  public const BOOL = 'boolean';
  public const CALLABLE = 'callable';
  public const COUNTABLE = 'countable';
  public const DATETIMEABLE = 'datetimeable';
  public const FLOAT = 'float';
  public const INT = 'integer';
  public const ITERABLE = 'iterable';
  public const JSONABLE = 'jsonable';
  public const NULL = 'null';
  public const OBJECT = 'object';
  public const RESOURCE = 'resource';
  public const SCALAR = 'scalar';
  public const STRING = 'string';

  /** @type array  known alias => datatype map. */
  protected const _TYPE_TR = ['double' => self::FLOAT, 'NULL' => self::NULL];

  /**
   * Captures var_dump output and returns it as a string.
   * @see <http://php.net/var_dump>
   *
   * @param mixed ...$expressions  expressions to debub
   * @return string                debugging output
   */
  public static function debug(...$expressions) : string {
    if (empty($expressions)) {
      throw new ValueException(ValueException::NO_EXPRESSIONS);
    }

    ob_start();
    var_dump(...$expressions);
    return ob_get_clean();
  }

  /**
   * checks a value's type against one or more given types/pseudotypes/classnames.
   *
   * to specify types/psuedotypes, use the appropriate Value constant.
   * to specify classnames, use the ::class magic constant.
   *
   * @param mixed  $value     the value to test
   * @param string ...$types  list of types/classnames to check against
   * @return bool             true if value matches at least one of given types; false otherwise
   */
  public static function is($value, string ...$types) : bool {
    $valtype = self::type($value);

    foreach ($types as $type) {
      if (
        ($valtype === $type) ||
        ($value instanceof $type) ||
        (($type === self::CALLABLE) && is_callable($value)) ||
        (($type === self::COUNTABLE) && self::_isCountable($value)) ||
        (($type === self::DATETIMEABLE) && self::_isDateTimeable($value)) ||
        (($type === self::ITERABLE) && is_iterable($value)) ||
        (($type === self::JSONABLE) && self::_isJsonable($value)) ||
        (($type === self::OBJECT) && is_object($value)) ||
        (($type === self::SCALAR) && is_scalar($value))
      ){
        return true;
      }
    }

    return false;
  }

  /**
   * Like is(), but throws a TypeError on failure.
   *
   * Note, the stack trace will start from here:
   * look at the next line to see where it was actually triggered.
   *
   * @param string $name      name of given argument (used in Error message)
   * @param mixed  $value     the argument to test
   * @param string ...$types  list of types/classnames to check against
   * @throws TypeError        if argument fails type check
   */
  public static function hint(string $name, $value, string ...$types) : void {
    if (! self::is($value, ...$types)) {
      $l = implode('|', $types);
      $t = self::type($value);
      $m = "{$name} must be {$l}; {$t} provided";
      throw new TypeError($m, E_WARNING);
    }
  }

  /**
   * Gets a value's type, or classname if an object.
   *
   * @param mixed $value  the value to check
   * @return string       the value's type or classname
   */
  public static function type($value) : string {
    return is_object($value) ?
      get_class($value) :
      strtr(gettype($value), self::_TYPE_TR);
  }

  /**
   * Checks if value is an array or instance of Countable.
   *
   * @param mixed $value  the value to check
   * @return bool         true if countable; false otherwise
   */
  protected static function _isCountable($value) : bool {
    return is_array($value) || $value instanceof Countable;
  }

  /**
   * Checks whether a value is a datetime value.
   *
   * @param mixed $value  the value to check
   * @return bool         true if value is a datetime value; false otherwise
   */
  protected static function _isDateTimeable($value) : bool {
    if ($value instanceof DateTimeInterface) {
      return true;
    }

    try {
      new DateTime($value);
      return true;
    } catch (Throwable $e) {
      return false;
    }
  }

  /**
   * checks whether a value can be serialized as json.
   * @see self::_filterJsonable()
   *
   * @param mixed $value  the value to check
   * @return bool         true if value is jsonable; false otherwise
   */
  protected static function _isJsonable($value) : bool {
    if (is_object($value)) {
      return ($value instanceof stdClass) || ($value instanceof JsonSerializable);
    }

    try {
      Json::encode($value);
      return true;
    } catch (Throwable $e) {
      return false;
    }
  }
}
