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
namespace at\util\exceptable\api;

/**
 * augmented interface for exceptions.
 *
 * @method string    Throwable::__toString( void )
 * @method int       Throwable::getCode( void )
 * @method string    Throwable::getFile( void )
 * @method int       Throwable::getLine( void )
 * @method string    Throwable::getMessage( void )
 * @method Throwable Throwable::getPrevious( void )
 * @method array     Throwable::getTrace( void )
 * @method string    Throwable::getTraceAsString( void )
 *
 * @method mixed JsonSerializable::jsonSerialize( void )
 */
interface Exceptable extends \Throwable, \JsonSerializable {

  /**
   * @type int  default exception code for unknown/generic exception cases.
   */
  const DEFAULT_CODE = 0;

  /**
   * gets information about a code known to the implementing class.
   *
   * @param int $code            the exception code to look up
   * @throws UnderflowException  if the code is not known to the implementation
   * @return array               a map of info about the code,
   *                             including (at a minimum) its "code", "severity", and "message".
   */
  public static function get_info(int $code) : array;

  /**
   * checks whether the implementation has info about the given code.
   *
   * @param int $code  the code to check
   * @return bool      true if the code is known; false otherwise
   */
  public static function has_info(int $code) : bool;

  /**
   * @param string    $0  exception message
   *                      if omitted, a message must be set based on the exception code
   * @param int       $1  exception code
   *                      if omitted, a default code must be set
   * @param Throwable $2  previous exception
   * @param array     $3  additional implementation-specific info
   */
  public function __construct(...$args);

  /**
   * traverses the chain of previous exception(s) and gets the root exception.
   *
   * @return Throwable  the root exception
   */
  public function getRoot() : \Throwable;

  /**
   * gets exception severity.
   *
   * @return int  the exception severity
   */
  public function getSeverity() : int;
}
