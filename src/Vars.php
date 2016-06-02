<?php
/**
 * @package    at.util
 * @version    0.4
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GPL-3.0 (no other versions permitted)
 *
 *  This program is free software: you can redistribute it and/or modify it
 *  under the terms of the GNU General Public License, version 3.
 *  You MAY NOT apply the terms of any other version of the GPL.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program.
 *  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 */
declare( strict_types = 1 );
namespace at\util;

/**
 * general variable handling utilities. */
class Vars {

  /**
   * captures var_dump output and returns it as a string.
   * @see <http://php.net/var_dump>
   *
   * @return string  debugging information about the expression(s)
   */
  public static function debug( ...$expressions ) {
    if ( empty( $expressions ) ) {
      $m = 'at least one $expression must be provided';
      throw new \BadMethodCallException( $m, E_USER_WARNING );
    }
    ob_start();
    var_dump( ...$expressions );
    return ob_get_clean();
  }

  /**
   * gets a variable's type, or classname if an object.
   *
   * @param mixed $var  the variable to check
   * @return string     the variable's type or classname
   */
  public static function type( $var ): string {
    return (is_object( $var )) ?
      get_class( $var ):
      strtolower( gettype( $var ) );
  }

  /**
   * checks a variable's type against one or more given types/fully qualified classnames,
   * and throws if it does not match any.
   *
   * intended for use where a union type hint (if such a thing existed) might be desired.
   *
   * @param mixed     $arg    the argument to test
   * @throws TypeError        if argument fails type check
   * @param string[] …$types  list of types/classnames to check against.
   */
  public static function typeHint( $arg, string ...$types ) {
    foreach ( $types as $type ) {
      if (
        ($arg instanceof $type)
        || ((strtolower( $type ) === 'callable') && is_callable( $arg ))
        || (strcasecmp( gettype( $arg ), $type ) === 0)
      ) {
        return;
      }
    }
    $l = implode( ', ', $types );
    $t = self::type( $arg );
    $m = "argument must be one of [{$l}]; [{$t}] provided";
    throw new \TypeError( $m, E_WARNING );
  }
}
