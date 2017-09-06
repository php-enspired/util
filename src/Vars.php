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
    Traversable,
    TypeError;

use at\exceptable\Handler;

use at\PRO\PRO;

use at\util\ {
  DateTime,
  VarsException
};

/**
 * general variable handling utilities.
 *
 * the dependency on at\PRO is "soft" (things work just fine if it doesn't exist).
 */
class Vars {

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
  const DATETIME = 'datetime';
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
   * @type int    COERCE_ARRAY   filter flag: coerce filter value to array.
   * @type int    REQUIRE_ARRAY  filter flag: require filter value to be an array.
   *
   * @type string OPT_DEFAULT    filter option key: default value.
   * @type string OPT_MIN        filter option key: minimum integer value.
   * @type string OPT_MAX        filter option key: maximum integer value.
   * @type string OPT_REGEX      filter option key: regular expression.
   */
  const COERCE_ARRAY = FILTER_FORCE_ARRAY;
  const REQUIRE_ARRAY = FILTER_REQUIRE_ARRAY;
  const OPT_DEFAULT = 'default';
  const OPT_MIN = 'min_range';
  const OPT_MAX = 'max_range';
  const OPT_REGEX = 'regex';


  /**
   * captures var_dump output and returns it as a string.
   * @see <http://php.net/var_dump>
   *
   * @return string  debugging information about the expression(s)
   */
  public static function debug(...$expressions) {
    if (empty($expressions)) {
      throw new VarsException(VarsException::NO_EXPRESSIONS);
    }
    ob_start();
    var_dump(...$expressions);
    return ob_get_clean();
  }

  /**
   * checks whether a given bit ("flag") is set on a bitmask.
   *
   * @param int $flag  flag to check for
   * @param int $mask  bitmask
   * @return bool      true if flag is set; false otherwise
   */
  public static function flag(int $flag, int $mask) : bool {
    return $flag & $mask === $flag;
  }

  /**
   * checks whether a variable is a datetime value.
   *
   * true for instances of DateTimeInterface, unix timestamps (integers/floats),
   * and strings which strtotime() understands.
   *
   * @param mixed $var  the variable to check
   * @return bool       true if variable is a datetime value; false otherwise
   */
  public static function isDateTimeable($var) : bool {
    return (is_string($var) && strtotime($var) !== false) ||
      self::typeCheck($var, DateTimeInterface::class, self::FLOAT, self::INT);
  }

  /**
   * checks whether a variable is iterable.
   * true for arrays and Traversable objects.
   *
   * @todo deprecate in favor of is_iterable() once support for php 7.0 is dropped.
   *
   * @param mixed $var  the variable to check
   * @return bool       true if variable is iterable; false otherwise
   */
  public static function isIterable($var) : bool {
    return ($var instanceof Traversable || is_array($var));
  }

  /**
   * checks whether a variable can be represented in json.
   * true for any variable type except resource;
   * with the additional restriction that objects must be stdClass or JSONSerializable.
   *
   * @param mixed $var  the variable to check
   * @return bool       true if variable is jsonable; false otherwise
   */
  public static function isJsonable($var) : bool {
    return is_object($var) ?
      ($var instanceof JSONSerializable) || ($var instanceof stdClass) :
      ! is_resource($var);
  }

  /**
   * checks whether a value is a valid regular expression or PRO instance.
   *
   * @param mixed $var  the variable to check
   * @return bool       true if variable is a regex; false otherwise
   */
  public static function isRegex($var) : bool {
    return $var instanceof PRO || (@preg_match($var, '') !== false);
  }

  /**
   * gets a variable's type, or classname if an object.
   *
   * @param mixed $var  the variable to check
   * @return string     the variable's type or classname
   */
  public static function type($var): string {
    return is_object($var) ?
      get_class($var) :
      strtr(gettype($var), self::TYPE_TR);
  }

  /**
   * checks a variable's type against one or more given types/fully qualified classnames.
   *
   * to specify types/psuedotypes, use the appropriate Vars constant.
   * to specify classnames, use the ::class magic constant.
   *
   * @param mixed  $arg     the value to test
   * @param string …$types  list of types/classnames to check against
   * @return bool           true if value matches at least one of given types; false otherwise
   */
  public static function typeCheck($var, string ...$types) {
    $argtype = self::type($var);

    foreach ($types as $type) {
      $match = ($argtype === $type) ||
        (($type === self::CALLABLE) && is_callable($var)) ||
        (($type === self::DATETIME) && self::isDateTimeable($var)) ||
        (($type === self::ITERABLE) && self::isIterable($var)) ||
        (($type === self::JSONABLE) && self::isJsonable($var)) ||
        ($var instanceof $type);
      if ($match) {
        return true;
      }
    }
    return false;
  }

  /**
   * checks a variable's type against one or more given types/fully qualified classnames,
   * and throws if it does not match any.
   *
   * note, the stack trace will start from here:
   * look at the next line to see where it was actually triggered.
   *
   * @param string   $name    name of given argument (used in Error message)
   * @param mixed    $arg     the argument to test
   * @param string[] …$types  list of types/classnames to check against
   * @throws TypeError        if argument fails type check
   */
  public static function typeHint(string $name, $arg, string ...$types) {
    if (! self::typeCheck($arg, ...$types)) {
      $l = implode('|', $types);
      $t = self::type($arg);
      $m = "{$name} must be" . (count($types) > 1 ? ' one of ' : ' ') . "{$l}; {$t} provided";
      throw new TypeError($m, E_WARNING);
    }
  }
}
