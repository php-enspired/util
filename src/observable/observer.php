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
    at\util\observable\Trigger;
use Ds\Map,
    Ds\Set;

/**
 * base implementation of Observer (SplObserver).
 *
 * when an update is recieved, in addition to invoking registered event handlers,
 * will invoke declared methods named "on{event name}".
 */
trait observer {

  /**
   * @type Map $_handlers   handler => trigger list map */
  protected $_handlers;

  /**
   * trait constructor.
   *
   * @param array $on             map of handler => triggers to register
   * @throws ObservableException  if invalid arguments are provided for on()
   */
  public function __construct( array $on=[] ) {
    $this->_handlers = new Map();

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
    $triggers = array_map(
      function( $trigger ) { return new Trigger( $trigger ); },
      ((array) ($triggers ?: []))
    );
    switch ( true ) {
      default:
        $error = ObservableException::get_info( ObservableException::TYPEERROR_OFF );
        throw new \TypeError( $error['message'], $error['code'] );
      case isset( $handler, $triggers ):
        return $this->_offTriggers( $handler, $triggers );
      case isset( $handler ):
        return $this->_offHandler( $handler );
      case isset( $triggers ):
        return $this->_offHandlers( $triggers );
    }
  }

  /**
   * @see api\Observer */
  public function on( callable $handler, $triggers ) {
    $triggers = array_map(
      function( $trigger ) { return new Trigger( $trigger ); },
      ((array) $triggers)
    );
    if ( empty( $triggers ) ) {
      throw new ObservableException( ObservableException::NO_TRIGGERS );
    }
    $triggerList = ($this->_handlers->hasKey( $handler )) ?
      $this->_handlers->get( $handler ) :
      new Set();
    $triggerList->add( ...$triggers );
    $this->_handlers->put( $handler, $triggerList );
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
        if ( $trigger->matches( $event ) ) {
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
    $this->_handlers->remove( $handler );
  }

  /**
   * unregisters triggers from all registered handlers.
   *
   * @param Trigger[] $triggers  list of trigger(s) to remove
   */
  protected function _offHandlers( array $triggers ) {
    foreach ( $this->_handlers as $handler ) {
      $this->_offTriggers( $handler, $triggers );
    }
  }

  /**
   * unregisters triggers from a handler.
   *
   * @param callable  $handler   the handler to remove
   * @param Trigger[] $triggers  list of trigger(s) to remove
   */
  protected function _offTriggers( callable $handler, array $triggers ) {
    $triggerList = $this->_handlers->get( $handler, null );
    if ( ! $triggerList ) {
      return;
    }
    foreach ( $triggers as $trigger ) {
      $triggerList->remove( $trigger );
    }
    if ( $triggerList->count() === 0 ) {
      $this->_offHandler( $handler );
    }
  }
}
