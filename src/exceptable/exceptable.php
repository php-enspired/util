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
namespace at\util\exceptable;

use at\util\exceptable\api\Exceptable as ExceptableAPI,
    at\util\JSON;

/**
 * base implementation for Exceptable interface.
 *
 * caution:
 *  - the implementing class must extend from a Throwable class (e.g., RuntimeException).
 *  - if the implementing class needs to extend ErrorException
 *    (which already has a (final) method getSeverity()),
 *    exceptable::getSeverity() will need to be aliased when the trait is used.
 *  - implementations cannot extend from PDOException,
 *    because its implementation of getCode() returns a string.
 */
trait exceptable {

  /**
   * @see <http://php.net/Exception.__toString> */
  abstract public function __toString();

  /**
   * @const array INFO {
   *    @type array ${$code} {
   *      @type string $message   the exception message
   *      @type int    $severity  the exception severity (one of the E_* constants)
   *      @type mixed  $...       implementation-specific additional info
   *    }
   *    ...
   *  }
   */

  /**
   * @type array $_extra  list of "extra" callbacks {@see exceptable::_extra()}
   */
  protected $_extra = ['_tr'];

  /**
   * @type int       $_code      the exception code
   * @type string    $_message   the exception message
   * @type int       $_severity  the exception severity
   */
  protected $_code;
  protected $_message;
  protected $_severity;

  /**
   * @see Exceptable::get_info() */
  public static function get_info(int $code) : array {
    if (! static::has_info($code)) {
      $m = "no exception code [{$code}] is known";
      throw new \UnderflowException($m, E_USER_WARNING);
    }
    return static::INFO[$code] + ['code' => $code];
  }

  /**
   * @see Exceptable::has_info() */
  public static function has_info(int $code) : bool {
    return isset(static::INFO[$code]);
  }

  /**
   * @see Exceptable::__construct() */
  public function __construct(...$args) {
    $context = (is_array(end($args))) ? array_pop($args) : [];
    $previous = (end($args) instanceof \Throwable) ? array_pop($args) : null;
    $this->_code = (is_int(end($args))) ? array_pop($args) : ExceptableAPI::DEFAULT_CODE;
    $this->_message = (is_string(end($args))) ?
      array_pop($args) :
      static::get_info($this->_code)['message'];

    // bad args: an exceptional exception.
    // what we could parse from the args becomes the new previous exception.
    if (! empty($args)) {
      $previous = new static($this->_message, $this->_code, $previous, $context);
      $message = "arguments passed to Exceptable::__construct are invalid and/or out of order:\n"
        . JSON::encode($args, [JSON::PRETTY]);

      throw new \RuntimeException($message, E_ERROR, $previous);
    }

    $this->_extra($context);
    parent::__construct($this->_message, $this->_code, $previous);
    $this->_setSeverity($context['severity'] ?? 0);
  }

  /**
   * @see Exceptable::getRoot() */
  public function getRoot() : \Throwable {
    $root = $this;
    while ($root->getPrevious() !== null) {
      $root = $root->getPrevious();
    }
    return $root;
  }

  /**
   * @see Exceptable::getSeverity() */
  public function getSeverity() : int {
    return $this->_severity;
  }

  /**
   * @see JsonSerializable::jsonSerialize() */
  public function jsonSerialize() {
    return [static::class => $this->__toString()];
  }

  /**
   * adds an "extra" callback to the implementation.
   *
   * @param mixed $callback  callable or instance method name to register
   */
  protected function _addExtra($callback) {
    $this->_extra[] = $callback;
  }

  /**
   * handles extra context info passed to constructor.
   *
   * default implementation does substitution for exception messages when a "tr" map is provided.
   * implementations may override this as needed to provide additional/alternate functionality.
   *
   * @param array $context  contextual info provided to constructor
   */
  protected function _extra(array $context) {
    $info = static::get_info($this->_code);

    foreach ($this->_extra as $extra) {
      if (method_exists($this, $extra)) {
        $extra = [$this, $extra];
      }
      if (is_callable($extra)) {
        $extra($info, $context);
      }
    }
  }

  /**
   * sets exception severity. if no severity provided, falls back on:
   *  - severity from exception info
   *  - severity from previous exception
   *  - E_ERROR
   *
   * @param int $severity  one of E_ERROR|E_WARNING|E_NOTICE|E_DEPRECATED
   */
  protected function _setSeverity(int $severity) {
    if (in_array($severity, [E_ERROR, E_WARNING, E_NOTICE, E_DEPRECATED])) {
      $this->_severity = $severity;
      return;
    }

    if (static::has_info($this->_code)) {
      $this->_severity = static::get_info($this->_code)['severity'];
      return;
    }

    $e = $this;
    while ($e->getPrevious() !== null) {
      $e = $e->getPrevious();
      if (method_exists($e, 'getSeverity')) {
        $this->_severity = $e->getSeverity();
        return;
      }
    }

    $this->_severity = E_ERROR;
  }

  /**
   * sets a translated exception message if sufficient contextual info was provided.
   *
   * @param array $info     default exception info
   * @param array $context  extra info provided to constructor
   */
  protected function _tr(array $info, array $context) {
    if (! isset($info['tr'], $info['tr_message'])) {
      return;
    }

    $tr = [];
    foreach ($info['tr'] as $key => $value) {
      $value = $context[$key] ?? $value;
      if ($value === null) {
        return;
      }
      $tr["%{$key}%"] = $value;
    }
    $this->_message = strtr($info['tr_message'], $tr);
  }
}
