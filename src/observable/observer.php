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
namespace at\util\observable;

use at\util\observable\ObservableException,
    at\util\Set;

/**
 * base implementation of Observer (SplObserver).
 *
 * when an update is recieved, in addition to invoking registered event handlers,
 * will invoke declared methods named "on{event name}".
 */
trait observer {

  /**
   * parses a string as a trigger regex.
   *
   * @param string $trigger  the event regex or literal event name
   * @return string          the parsed event regex
   */
  abstract protected function _parseTrigger( string $trigger ) : string;

  /**
   * @type SplObjectStorage $_handlers   handler => trigger events map */
  protected $_handlers;

  /**
   * trait constructor.
   *
   * @param array $on  map of handler => triggers to register
   */
  public function __construct( array $on=[] ) {
    $this->_handlers = new \SplObjectStorage();

    try {
      foreach ( $on as $handler=>$triggers ) {
        $this->on( $handler, $triggers );
      }
    } catch ( \Throwable $e ) {
      throw new ObservableException( ObservableException::WRONG_ON_ARGS, $e );
    }
  }

  /**
   * @see api\Observer */
  public function off( callable $handler=null, $triggers=null ) {
    switch ( true ) {
      default:
        $error = ObservableException::get_info( ObservableException::TYPEERROR_OFF );
        throw new \TypeError( $error['message'], $error['code'] );
      case isset( $handler, $triggers ):
        settype( $triggers, 'array' );
        return $this->_offTriggers( $handler, $triggers );
      case isset( $handler ):
        return $this->_offHandler( $handler );
      case isset( $triggers ):
        settype( $triggers, 'array' );
        return $this->_offHandlers( $triggers );
    }
  }

  /**
   * @see api\Observer */
  public function on( callable $handler, $triggers ) {
    if ( is_string( $triggers ) ) {
      settype( $triggers, 'array' );
    }
    if ( ! is_array( $triggers ) ) {
      $error = ObservableException::get_info( ObservableException::TYPEERROR_ON );
      throw new \TypeError( $error['message'], $error['code'] );
    }
    $triggerList = ($this->_handlers->offsetExists( $handler )) ?
      $this->_handlers->offsetGet( $handler ) :
      new Set();

    try {
      foreach ( $triggers as $trigger ) {
        $triggerList->offsetSet( $this->_parseTrigger( $trigger ) );
      }
    } catch ( \Throwable $e ) {
      throw new ObservableException( ObservableException::INVALID_TRIGGER, $e );
    }
    if ( $triggerList->count() === 0 ) {
      throw new ObservableException( ObservableException::NO_TRIGGERS );
    }
    $this->_handlers->offsetSet( $handler, $triggerList );
  }

  /**
   * @see api\Observer */
  public function update( \SplSubject $subject ) {
    $args = func_get_args() + [1 => 'update'];
    array_shift( $args );
    $event = array_shift( $args );

    try {
      foreach ( $this->_getHandlers( $event ) as $handler ) {
        $handler( $subject, $event, ...$args );
      }
    } catch ( \Throwable $e ) {
      throw new ObservableException(
        ObservableException::UNCAUGHT_EXCEPTION,
        $e,
        [
          'subject' => static::class,
          'event' => $event,
          'message' => $e->getMessage()
        ]
      );
    }
  }

  /**
   * gets registered/declared update handlers, filtered by event name.
   *
   * @param string $event  the event name to filter by
   * @return array         list of update handlers
   */
  protected function _getHandlers( string $event ) {
    $handlers = [];
    $method = 'on' . preg_replace( '(\W+)', '_', $event );
    if ( is_callable( [$this, $method] ) ) {
      $handlers[] = [$this, $method];
    }
    foreach ( $this->_handlers as $handler => $triggerList ) {
      foreach ( $triggerList as $trigger ) {
        if ( preg_match( $trigger, $event ) === 1 ) {
          $handlers[] = $handler;
          break;
        }
      }
    }
    return $handlers;
  }

  /**
   * unregisters a handler.
   *
   * @param callable $handler  the handler to remove
   */
  protected function _offHandler( callable $handler ) {
    if ( ! $this->_handlers->offsetExists( $handler ) ) {
      return;
    }
    $this->_handlers->offsetUnset( $handler );
  }

  /**
   * unregisters triggers from all registered handlers.
   *
   * @param string[] $triggers  list of trigger(s) to remove
   */
  protected function _offHandlers( array $triggers ) {
    foreach ( $this->_handlers as $handler ) {
      $this->_offTriggers( $handler, $triggers );
    }
  }

  /**
   * unregisters triggers from a handler.
   *
   * @param callable $handler   the handler to remove
   * @param string[] $triggers  list of trigger(s) to remove
   */
  protected function _offTriggers( callable $handler, array $triggers ) {
    if ( ! $this->_handlers->offsetExists( $handler ) ) {
      return;
    }
    $triggers = array_map([$this, '_parseTrigger'], $triggers);
    $triggerList = $this->_handlers->offsetGet( $handler );
    foreach ( $triggerList as $trigger ) {
      if ( in_array( $trigger, $triggers ) ) {
        $triggerList->offsetUnset( $trigger );
      }
    }
    if ( $triggerList->count() === 0 ) {
      $this->_offHandler( $handler );
    }
  }
}
