<?php
/**
 * @package    at.util
 * @version    0.4[20160424]
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GNU GPL V2 <http://gnu.org/licenses/gpl-2.0.txt>
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
