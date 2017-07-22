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

use at\util\ {
  DateTime,
  VarToolsException
};

/**
 * general variable handling utilities.
 */
class VarTools {

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
   * @type int   OPT_FLAGS    "flags" key for filter() $opts tuple
   * @type array OPT_OPTIONS  "options" key for filter() $opts tuple
   */
  const OPT_FLAGS = 0;
  const OPT_OPTIONS = 1;


  /**
   * captures var_dump output and returns it as a string.
   * @see <http://php.net/var_dump>
   *
   * @return string  debugging information about the expression(s)
   */
  public static function debug(...$expressions) {
    if (empty($expressions)) {
      throw new VarToolsException(VarToolsException::NO_EXPRESSIONS);
      $m = 'at least one $expression must be provided';
      throw new \BadMethodCallException($m, E_USER_WARNING);
    }
    ob_start();
    var_dump(...$expressions);
    return ob_get_clean();
  }

  /**
   * filters variables based on a callback map.
   *
   * for details about building filter definitions, @see http://php.net/filter_var
   * in addition, allows "shorthand" filter definitions:
   *  - callable: will use FILTER_CALLBACK
   *  - NULL: will use FILTER_DEFAULT
   *  - FILTER_VALIDATE_EMAIL: will use Validator::email (handles internationalized emails)
   *  - Validator::{type} constants will filter for that type
   *
   * callable filters use the following signature:
   *  filter_callback(mixed $value) : mixed
   *
   * @param mixed $value        the value to filter
   * @param mixed $filter       filter definition
   * @param array $opts         {
   *    @type int   OPT_FLAGS    filter flags
   *    @type array OPT_OPTIONS  filter options
   *  }
   * @throws VarToolsException  if a provided callback throws, or if filter definition is invalid
   * @return mixed              the filtered variable on success; or null on failure
   */
  public static function filter($value, $filter = null, array $opts = []) {
    if ($filter === null) {
      $filter = FILTER_DEFAULT;
    }
    list($flags, $options) = ($opts + [self::OPT_FLAGS => 0, self::OPT_OPTIONS => []]);
    self::typeHint('$opts[OPT_FLAGS]', $flags, self::INT);
    $flags |= FILTER_NULL_ON_FAILURE;
    self::typeHint('$opts[OPT_OPTIONS]', $options, self::ARRAY);


    $filterMap = [
      self::BOOL => FILTER_VALIDATE_BOOLEAN,
      self::FLOAT => FILTER_VALIDATE_FLOAT,
      self::INT => FILTER_VALIDATE_INT
    ];

    switch ($filter) {
      case isset($filterMap[$filter]) :
        $filter = $filterMap[$filter];
        break;
      case self::ARRAY :
      case self::ITERABLE :
        if ($value instanceof Traversable) {
          $value = iterator_to_array($value);
        }
        return is_array($value) ? $value : null;
      case FILTER_VALIDATE_EMAIL :
        return (Validator::email($value) === $value) ? $value : null;
      case self::DATETIME :
        try {
          return ($value instanceof DateTimeInterface) ? $value : DateTime::create($value);
        } catch (Throwable $e) {
          return null;
        }
      case is_callable($filter) :
        $options = $filter;
        $filter = FILTER_CALLBACK;
        break;
      case self::STRING :
        return (is_scalar($value) || method_exists($value, '__toString')) ?
          strval($value) :
          null;
      case is_string($filter) :
        // also covers self::CALLABLE|JSONABLE|NULL|OBJECT|RESOURCE
        return self::typeCheck($value, $filter) ? $value : null;
      default: break;
    }

    try {
      $errorExceptions = (new Handler)->throw(E_ALL)->register();
      return filter_var($value, $filter, ['flags' => $flags, 'options' => $options]);
    } catch (\Throwable $e) {
      throw new VarToolsException(VarToolsException::BAD_CALL_RIPLEY, $e);
    } finally {
      $errorExceptions->unregister();
    }
  }

  /**
   * checks whether a variable is a datetime value.
   *
   * true for instances of DateTimeInterface, unix timestamps (integers),
   * and strings which are valid for the $time argument of DateTime::__construct().
   *
   * using self::filter() is faster.
   *
   * @param mixed $var  the variable to check
   * @return bool       true if variable is a datetime value; false otherwise
   */
  public static function isDateTimeable($var) : bool {
    return self::filter($var, self::DATETIME) !== null;
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
  public static function isIterable($var) {
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
  public static function isJsonable($var) {
    return is_object($var) ?
      ($var instanceof JSONSerializable) || ($var instanceof stdClass) :
      ! is_resource($var);
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
   * @param mixed  $arg     the argument to test
   * @param string …$types  list of types/classnames to check against
   * @return bool           true if arg matches one of given types; false otherwise
   */
  public static function typeCheck($arg, string ...$types) {
    $argtype = self::type($arg);

    foreach ($types as $type) {
      $match = ($argtype === $type) ||
        (($type === self::CALLABLE) && is_callable($arg)) ||
        (($type === self::ITERABLE) && self::is_iterable($arg)) ||
        (($type === self::JSONABLE) && self::is_jsonable($arg)) ||
        ($arg instanceof $type);
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
