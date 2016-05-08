<?php
/**
 * @package    at.util
 * @version    0.4
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GPL-3.0 (no later versions permitted)
 *
 *  This program is free software: you can redistribute it and/or modify it
 *  under the terms of the GNU General Public License, version 3.
 *  You MAY NOT apply the terms of any later version of the GPL.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program.
 *  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 */
declare( strict_types = 1 );
namespace at\util\exceptable\api;

/**
 * augmented interface for exceptions.
 * the implementing class must extend from a Throwable class (e.g., RuntimeException).
 *
 * caution:
 *  - if the implementing class needs to extend ErrorException
 *    (which already has a (final) method getSeverity()),
 *    exceptable::getSeverity() will need to be aliased when the trait is used.
 *  - implementations cannot extend from PDOException,
 *    because its implementation of getCode() returns a string.
 */
interface Exceptable extends \Throwable {

  /**
   * gets the default exception code for the implementing class.
   *
   * @return int  the default exception code
   */
  public static function get_default_code() : int;

  /**
   * gets information about a code known to the implementing class.
   *
   * @param int $code            the exception code to look up
   * @throws UnderflowException  if the code is not known to the implementation
   * @return array               a map of info about the code,
   *                             including (at a minimum) its "code", "severity", and "message".
   */
  public static function get_info( int $code ) : array;

  /**
   * checks whether the implementation has info about the given code.
   *
   * @param int $code  the code to check
   * @return bool      true if the code is known; false otherwise
   */
  public static function has_info( int $code ) : bool;

  /**
   * @param string    $0  exception message
   *                      if omitted, a message must be set based on the exception code
   * @param int       $1  exception code
   *                      if omitted, a default code must be set
   * @param Throwable $2  previous exception
   * @param array     $3  additional implementation-specific info
   */
  public function __construct( ...$args );

  /**
   * traverses the chain of previous exception(s) and gets the root exception.
   *
   * @return Throwable  the root exception
   */
  public function getRoot() : \Throwable;

  /**
   * gets exception severity.
   *
   * @return int  the exception severity (one of the E_** constants)
   */
  public function getSeverity() : int;
}
