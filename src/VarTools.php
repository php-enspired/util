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

use DateTimeImmutable;
use DateTimeInterface;
use GMP;
use Throwable;
use TypeError;

use at\util\VarToolsException;

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
   * @type string FLOAT     float type
   * @type string INT       integer type
   * @type string ITERABLE  arrays or Traversable objects
   * @type string JSONABLE  JsonSerializable or stdClass objects; any other type except resource
   * @type string NULL      null type
   * @type string OBJECT    object type
   * @type string RESOURCE  resource type
   * @type string STRING    string type
   */
  const ARRAY = 'array';
  const BOOL = 'boolean';
  const CALLABLE = 'callable';
  const FLOAT = 'float';
  const INT = 'integer';
  const ITERABLE = 'iterable';
  const JSONABLE = 'jsonable';
  const NULL = 'null';
  const OBJECT = 'object';
  const RESOURCE = 'resource';
  const STRING = 'string';

  /** @type array  known alias => datatype map. */
  const TYPE_TR = ['double' => self::INTEGER, 'NULL' => self::NULL];

  /**
   * casts a value to a specific type.
   *
   * @param mixed  $value  the value to cast
   * @param string $type   the type to cast to
   * @param array  $opts {
   *    @type mixed $default|$0  default value if casting is not possible
   *    @type bool  $throw|$1    throw if casting is not possible?
   *  }
   * @throws VarToolsException  if type, cast, or default is invalid
   * @return mixed              the casted value on success; default value otherwise
   */
  public static function cast($value, string $type, array $opts = []) {
    if (! method_exists([__CLASS__, "to_{$type}"])) {
      throw new VarToolsException(VarToolsException::INVALID_CAST_TYPE);
    }

    return self::{"to_{$type}"}($value, $opts);
  }

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
   * {@see http://php.net/filter_var_array} for details about building filter definitions.
   * in addition, allows "shorthand" filter definitions:
   *  - if $definition is callable, it will be applied to the entire array using FILTER_CALLBACK.
   *  - if a $definition value is NULL, that key will use FILTER_DEFAULT.
   *  - if a $definition value is callable, that key will use FILTER_CALLBACK.
   *  - if a $definition value is a Regex instance, that key will use FILTER_VALIDATE_REGEXP.
   *
   * callable filters use the following signature:
   *  filter_callback(mixed $value) : mixed
   *
   * like filter_var_array, values can be arrays, but filter definitions cannot be nested.
   * to validate an item with nested arrays,
   * pass a callback function to the base key which contains the validation logic.
   *
   * @param array $vars         the variables to filter
   * @param mixed $definition   filter definition
   * @param bool  $add_empty    add missing items (as NULL) to the returned array
   * @throws VarToolsException  if a provided callback throws, or if filter definition is invalid
   * @return array              the filtered variables
   */
  public static function filter(array $vars, $definition, $add_empty=true): array {
    try {
      if ($definition === null) {
        $definition = FILTER_DEFAULT;
      } elseif (is_callable($definition)) {
        $definition = array_fill_keys(array_keys($vars), $definition);
      }
      if (is_array($definition)) {
        foreach ($definition as &$i) {
          if ($i === null) {
            $i = FILTER_DEFAULT;
          } elseif ($i instanceof Regex) {
            $i = [
              "filter"  => FILTER_VALIDATE_REGEXP,
              "options" => ["regexp" => $i->__toString()]
            ];
          } elseif (is_callable($i)) {
            $i = [
              "filter"  => FILTER_CALLBACK,
              "options" => $i
            ];
          }
        }
      }

      $result = filter_var_array($vars, $definition, $add_empty);
    } catch (\Throwable $e) {
      throw new VarToolsException(VarToolsException::BAD_CALL_RIPLEY, $e);
    }
    if (! is_array($result)) {
      throw new VarToolsException(
        VarToolsException::INVALID_FILTER,
        ['definition' => Json::encode($definition)]
      );
    }
    return $result;
  }

  /**
   * checks whether a variable can be coverted to DateTime.
   *
   * @param mixed $var  the variable to check
   * @return bool       true if variable is DateTimeable; false otherwise
   */
  public static function is_DateTimeable($var) : bool {
    try {
      self::to_DateTime($var);
      return true;
    } catch (Throwable $e) {
      return false;
    }
  }

  /**
   * checks whether a variable is iterable.
   *
   * true for arrays and objects which implement Traversable.
   *
   * @param mixed $var  the variable to check
   * @return bool       true if variable is iterable; false otherwise
   */
  public static function is_iterable($var) {
    return ($var instanceof \Traversable || is_array($var));
  }

  /**
   * checks whether a variable can be represented in json.
   *
   * true for any variable type except resource;
   * with the additional restriction that objects must be stdClass or JSONSerializable.
   *
   * @param mixed $var  the variable to check
   * @return bool       true if variable is jsonable; false otherwise
   */
  public static function is_jsonable($var) {
    return is_object($var) ?
      ($var instanceof \JSONSerializable) || ($var instanceof \stdClass) :
      ! is_resource($var);
  }

  /**
   * casts a value to array.
   *
   * @param mixed $value  the value to cast
   * @param array  $opts {
   *    @type mixed $default|$0  default value if casting is not possible
   *  }
   * @throws VarToolsException  if cast or default is invalid
   * @return array              the casted value on success; default value otherwise
   */
  public static function to_array($value, array $opts = []) {
    $default = $opts['default'] ?? $opts[0] ?? [];
    if (! is_array($default)) {
      throw new VarToolsException(
        VarToolsException::INVALID_CAST_DEFAULT,
        ['type' => self::ARRAY, 'default' => self::type($default)]
      );
    }

    return empty($value) ? $default : (array) $value;
  }

  /**
   * casts a value to boolean.
   *
   * @param mixed $value  the value to cast
   * @return boolean  the casted value
   */
  public static function to_boolean($value) {
    return is_string($value) ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : (bool) $value;
  }

  /**
   * casts a value to callable.
   *
   * @param mixed $value  the value to cast
   * @param array  $opts {
   *    @type mixed $default|$0  default value if casting is not possible
   *    @type bool  $throw|$1    throw if casting is not possible?
   *  }
   * @throws VarToolsException  if cast or default is invalid
   * @return callable           the casted value on success; default value otherwise
   */
  public static function to_callable($value, array $opts = []) {
    $default = $opts['default'] ?? $opts[0] ?? function() use ($value) { return $value; };
    if (! is_callable($default)) {
      throw new VarToolsException(
        VarToolsException::INVALID_CAST_DEFAULT,
        ['type' => self::CALLABLE, 'default' => self::type($default)]
      );
    }
    $throw = (bool) ($opts['throw'] ?? $opts[1] ?? false);

    return is_callable($value) ? $value : $default;
  }

  /**
   * casts a value to DateTime.
   *
   * @param mixed $value  the value to cast
   * @param array  $opts {
   *    @type mixed $default|$0  default value if casting is not possible
   *    @type bool  $throw|$1    throw if casting is not possible?
   *  }
   * @throws VarToolsException  if cast or default is invalid
   * @return DateTimeInterface  the casted value on success; default value otherwise
   */
  public static function to_DateTime($value, array $opts = []) : DateTimeInterface {
    $default = $opts['default'] ?? $opts[0] ?? new DateTimeImmutable;
    if (! $default instanceof DateTimeInterface) {
      throw new VarToolsException(
        VarToolsException::INVALID_CAST_DEFAULT,
        ['type' => DateTimeInterface::class, 'default' => self::type($default)]
      );
    }
    $throw = (bool) ($opts['throw'] ?? $opts[1] ?? false);

    try {
      if (! $value instanceof DateTimeInterface) {
        $unixtime = filter_var($value, FILTER_VALIDATE_INT);
        $value = new DateTimeImmutable(is_int($unixtime) ? "@{$unixtime}" : $value);
      }
      return $value;
    } catch (Throwable $e) {
      if (! $throw) {
        return $default;
      }
      throw new VarToolsException(VarToolsException::INVALID_TIME_VALUE, ['value' => $value]);
    }
  }

  /**
   * casts a value to float.
   *
   * @param mixed $value  the value to cast
   * @param array  $opts {
   *    @type mixed $default|$0  default value if casting is not possible
   *    @type bool  $throw|$1    throw if casting is not possible?
   *  }
   * @throws VarToolsException  if cast or default is invalid
   * @return float              the casted value on success; default value otherwise
   */
  public static function to_float($value, array $opts = []) {
    $default = $opts['default'] ?? $opts[0] ?? 0.0;
    if (! is_float($default)) {
      throw new VarToolsException(
        VarToolsException::INVALID_CAST_DEFAULT,
        ['type' => self::FLOAT, 'default' => self::type($default)]
      );
    }
    $throw = (bool) ($opts['throw'] ?? $opts[1] ?? false);

    switch (gettype($value)) {
      case 'double' :
        return $value;
      case 'object' :
        if (! method_exists($value, '__toString')) {
          break;
        }
        // fall through
      case 'integer' :
      case 'string' :
        $float = filter_var((string) $value, FILTER_VALIDATE_FLOAT);
        if (is_float($float)) {
          return $float;
        }
        break;
      default : break;
    }

    if ($throw) {
      throw new VarToolsException(
        VarToolsException::UNCASTABLE,
        ['type' => self::FLOAT, 'value' => self::type($value)]
      );
    }
    return $default;
  }

  /**
   * casts a value to integer.
   *
   * @param mixed $value  the value to cast
   * @param array  $opts {
   *    @type mixed $default|$0  default value if casting is not possible
   *    @type bool  $throw|$1    throw if casting is not possible?
   *  }
   * @throws VarToolsException  if cast or default is invalid
   * @return                    the casted value on success; default value otherwise
   */
  public static function to_integer($value, array $opts = []) {
    $default = $opts['default'] ?? $opts[0] ?? 0;
    if (! is_int($default)) {
      throw new VarToolsException(
        VarToolsException::INVALID_CAST_DEFAULT,
        ['type' => self::INTEGER, 'default' => self::type($default)]
      );
    }
    $throw = (bool) ($opts['throw'] ?? $opts[1] ?? false);

    switch (gettype($value)) {
      case 'integer' :
        return $value;
      case 'object' :
        if (! method_exists($value, '__toString')) {
          break;
        }
        // fall through
      case 'double' :
      case 'string' :
        $int = filter_var((string) $value, FILTER_VALIDATE_INT);
        if (is_int($int)) {
          return $int;
        }
        break;
      default : break;
    }

    if ($throw) {
      throw new VarToolsException(
        VarToolsException::UNCASTABLE,
        ['type' => self::INTEGER, 'value' => self::type($value)]
      );
    }
    return $default;
  }

  /**
   * casts a value to iterable.
   *
   * @param mixed $value  the value to cast
   * @param array  $opts {
   *    @type mixed $default|$0  default value if casting is not possible
   *  }
   * @throws VarToolsException  if cast or default is invalid
   * @return iterable           the casted value on success; default value otherwise
   */
  public static function to_iterable($value, array $opts = []) {
    $default = $opts['default'] ?? $opts[0] ?? [];
    if (! self::is_iterable($default)) {
      throw new VarToolsException(
        VarToolsException::INVALID_CAST_DEFAULT,
        ['type' => self::ITERABLE, 'default' => self::type($default)]
      );
    }

    if (self::is_iterable($value)) {
      return $value;
    }

    return empty($value) ? $default : self::to_array($value);
  }

  /**
   * casts a value to jsonable.
   *
   * @param mixed $value  the value to cast
   * @param array  $opts {
   *    @type mixed $default|$0  default value if casting is not possible
   *    @type bool  $throw|$1    throw if casting is not possible?
   *  }
   * @throws VarToolsException  if cast or default is invalid
   * @return jsonable           the casted value on success; default value otherwise
   */
  public static function to_jsonable($value, array $opts = []) {
    $default = $opts['default'] ?? $opts[0] ?? [];
    if (! self::is_jsonable($default)) {
      throw new VarToolsException(
        VarToolsException::INVALID_CAST_DEFAULT,
        ['type' => self::JSONABLE, 'default' => self::type($default)]
      );
    }
    $throw = (bool) ($opts['throw'] ?? $opts[1] ?? false);

    if (self::is_jsonable($value)) {
      return $value;
    }

    if ($throw) {
      throw new VarToolsException(
        VarToolsException::UNCASTABLE,
        ['type' => self::JSONABLE, 'value' => self::type($value)]
      );
    }
    return $default;
  }

  /**
   * casts a value to null.
   *
   * @param mixed $value  the value to cast
   * @return null
   */
  public static function to_null($value) {
    return null;
  }

  /**
   * casts a value to object.
   *
   * @param mixed $value  the value to cast
   * @return object       the casted value
   */
  public static function to_object($value) {
    return (object) $value;
  }

  /**
   * casts a value to resource.
   *
   * @param mixed $value  the value to cast
   * @param array  $opts {
   *    @type mixed $default|$0  default value if casting is not possible
   *    @type bool  $throw|$1    throw if casting is not possible?
   *  }
   * @throws VarToolsException  if cast or default is invalid
   * @return resource           the casted value on success; default value otherwise
   */
  public static function to_resource($value, array $opts = []) {
    $default = $opts['default'] ?? $opts[0] ?? null;
    if (! is_resource($default)) {
      throw new VarToolsException(
        VarToolsException::INVALID_CAST_DEFAULT,
        ['type' => self::RESOURCE, 'default' => self::type($default)]
      );
    }
    $throw = (bool) ($opts['throw'] ?? $opts[1] ?? false);

    if (is_resource($value)) {
      return $value;
    }

    if ($throw) {
      throw new VarToolsException(
        VarToolsException::UNCASTABLE,
        ['type' => self::RESOURCE, 'value' => self::type($value)]
      );
    }
    return $default;
  }

  /**
   * casts a value to string.
   *
   * @param mixed $value  the value to cast
   * @param array  $opts {
   *    @type mixed $default|$0  default value if casting is not possible
   *    @type bool  $throw|$1    throw if casting is not possible?
   *  }
   * @throws VarToolsException  if cast or default is invalid
   * @return string             the casted value on success; default value otherwise
   */
  public static function to_string($value, array $opts = []) {
    $default = $opts['default'] ?? $opts[0] ?? '';
    if (! is_($default)) {
      throw new VarToolsException(
        VarToolsException::INVALID_CAST_DEFAULT,
        ['type' => self::STRING, 'default' => self::type($default)]
      );
    }
    $throw = (bool) ($opts['throw'] ?? $opts[1] ?? false);

    switch (gettype($value)) {
      case 'object' :
        if (method_exists($value, '__toString')) {
          $value = $value->__toString();
        }
        // fall through
      case 'string' :
        return $value;
      case 'double' :
      case 'integer' :
        return (string) $value;
      default :
        break;
    }

    if ($throw) {
      throw new VarToolsException(
        VarToolsException::UNCASTABLE,
        ['type' => self::STRING, 'value' => self::type($value)]
      );
    }
    return $default;
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
