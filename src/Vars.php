<?php
/**
 * @package    at.util
 * @version    0.4
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GPL-3.0 (no later versions)
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

/**
 * general variable handling utilities.
 */
class Vars {

  /**
   * captures var_dump output and returns it as a string.
   * @see <http://php.net/var_dump>
   *
   * @return string  debugging information about the expression(s)
   */
  public static function debug(...$expressions) {
    if (empty($expressions)) {
      $m = 'at least one $expression must be provided';
      throw new \BadMethodCallException($m, E_USER_WARNING);
    }
    ob_start();
    var_dump(...$expressions);
    return ob_get_clean();
  }

  /**
   * checks whether a variable is iterable.
   *
   * true for arrays and objects which implement Traversable.
   *
   * @todo deprecate when 7.1 is lowest supported version
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
   * gets a variable's type, or classname if an object.
   *
   * @param mixed $var  the variable to check
   * @return string     the variable's type or classname
   */
  public static function type($var): string {
    return is_object($var) ?
      get_class($var) :
      strtr(gettype($var), ['double' => 'float', 'NULL' => 'null']);
  }

  /**
   * checks a variable's type against one or more given types/fully qualified classnames.
   *
   * understands the psuedotypes "callable," "iterable," and "jsonable."
   *
   * @param mixed  $arg     the argument to test
   * @param string …$types  list of types/classnames to check against
   * @return bool           true if arg matches one of given types; false otherwise
   */
  public static function typeCheck($arg, string ...$types) {
    $argtype = self::type($arg);

    foreach (array_map('strtolower', $types) as $type) {
      $match = ($argtype === $type)
        || ($arg instanceof $type)
        || (($type === 'callable') && is_callable($arg))
        || (($type === 'iterable') && self::is_iterable($arg))
        || (($type === 'jsonable') && self::is_jsonable($arg));
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
      $m = "{$name} must be one of {$l}; {$t} provided";
      throw new \TypeError($m, E_WARNING);
    }
  }
}
