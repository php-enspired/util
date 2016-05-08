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
namespace at\util\exceptable;

/**
 * base implementation for Exceptable interface. */
trait exceptable {

  /**
   * @const int   DEFAULT_CODE  the default code for the implementing exception class
   *
   * @const array INFO {
   *    @type array …$code {
   *      @type int    $code      the exception code
   *      @type string $message   the exception message
   *      @type int    $severity  the exception severity (one of the E_* constants)
   *      @type mixed  $…         implementation-specific additional info
   *    }
   *  }
   */

  /**
   * @type int       $_code      the exception code
   * @type string    $_message   the exception message
   * @type Throwable $_previous  the previous exception
   * @type int       $_severity  the exception severity
   */
  protected $_code;
  protected $_message;
  protected $_previous;
  protected $_severity;

  /**
   * @see Exceptable::get_default_code() */
  public static function get_default_code() {
    return static::DEFAULT_CODE;
  }

  /**
   * @see Exceptable::get_info() */
  public static function get_info( int $code ) : array {
    if ( ! static::has_info( $code ) ) {
      $m = "no exception code [{$code}] is known";
      throw new \UnderflowException( $m, E_USER_WARNING );
    }
    return static::INFO[$code];
  }

  /**
   * @see Exceptable::has_info() */
  public static function has_info( int $code ) : bool {
    return isset( static::INFO[$code] );
  }

  /**
   * @see Exceptable::__construct() */
  public function __construct( ...$args ) {
    $this->_message = (is_string( reset( $args ) )) ?
      array_shift( $args ) :
      null;
    $this->_code = (is_int( reset( $args ) )) ?
      array_shift( $args ) :
      static::get_default_code();
    $this->_previous = (reset( $args ) instanceof \Throwable) ?
      array_shift( $args ) :
      null;

    if ( is_array( reset( $args ) ) ) {
      $this->_extra( array_shift( $args ) );
    }

    parent::__construct( $this->_message, $this->_code, $this->_previous );
  }

  /**
   * @see Exceptable::getRoot() */
  public function getRoot() : \Throwable {
    $root = $this;
    while ( $root->getPrevious() !== null ) {
      $root = $root->getPrevious();
    }
    return $root;
  }

  /**
   * @see Exceptable::getSeverity() */
  public function getSeverity() : int {
    return $this->_severity ?? E_ERROR;
  }

  /**
   * handles extra context info passed to constructor.
   *
   * default implementation does substitution for exception messages when a "tr" map is provided.
   * implementations may override this as needed to provide additional/alternate functionality.
   *
   * @param array $context {
   *    @type array $tr        key => value map for translated exception message
   *    @type int   $severity  exception severity
   *    @type mixed $…         additional implementation-specific info
   *  }
   */
  protected function _extra( array $context ) {
    $info = static::get_info( $this->_code );

    $this->_message( $info, $context );
    $this->_severity( $info, $context );
  }

  /**
   * sets a translated exception message if contextual info was provided;
   * otherwise, sets a default message if no message was given in constructor.
   *
   * @param array $info     default exception info
   * @param array $context  extra info provided to constructor
   */
  protected function _message( array $info, array $context ) {
    if ( isset( $context['tr'], $info['tr'], $info['tr_message'] ) ) {
      foreach ( $info['tr'] as $key => $value ) {
        $tr["%{$key}%"] = $context['tr'][$key] ??
          $info['tr'][$key];
      }
      $this->_message = strtr( $info['tr_message'], $tr );
    } elseif ( $this->_message === null && isset( $info['message'] ) ) {
      $this->_message = $info['message'];
    }
  }

  /**
   * sets exception severity from context, previous exception(s), or default info.
   *
   * @param array $info     default exception info
   * @param array $context  extra info provided to constructor
   */
  protected function _severity( array $info, array $context ) {
    $severities = E_ERROR | E_WARNING | E_NOTICE | E_DEPRECATED;

    if ( isset( $context['severity'] ) && ($context['severity'] & $severities) ) {
      $this->_severity = $context['severity'];
      return;
    }
    if (
      $this->_previous
      && method_exists( $this->_previous, 'getSeverity' )
      && ($this->_previous->getSeverity() & $severities)
    ) {
      $this->_severity = $this->_previous->getSeverity();
      return;
    }
    if ( isset( $info['severity'] ) && ($info['severity'] & $severities) ) {
      $this->_severity = $info['severity'];
    }
  }
}
