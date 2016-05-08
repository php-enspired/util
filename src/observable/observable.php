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
 * base implementation of Observable (SplSubject). */
trait observable {

  /**
   * parses a string as a trigger regex.
   *
   * @param string $trigger  the event regex or literal event name
   * @return string          the parsed event regex
   */
  abstract protected function _parseTrigger( string $trigger ) : string;

  /**
   * @type SplObjectStorage  observer => subscribed events map */
  protected $_observers;

  /**
   * trait constructor. */
  public function __construct() {
    $this->_observers = new \SplObjectStorage();
  }

  /**
   * @see api\Observable */
  public function attach( \SplObserver $observer ) {
    $args = func_get_args();
    $triggers = $args[1] ?? '(^.*$)ui';
    settype( $triggers, 'array' );
    $append = $args[2] ?? true;

    $triggerList = ($append && $this->_observers->offsetExists( $observer )) ?
      $this->_observers->offsetGet( $observer ):
      new Set;

    try {
      foreach ( $triggers as $trigger ) {
        $triggerList->offsetSet( $this->_parseTrigger( $trigger ) );
      }
    } catch ( \Throwable $e ) {
      throw new ObservableException( ObservableException::INVALID_TRIGGER, $e );
    }
    $this->_observers->offsetSet( $observer, $triggerList );
  }

  /**
   * @see api\Observable */
  public function detach( \SplObserver $observer ) {
    if ( ! $this->_observers->offsetExists( $observer ) ) {
      return;
    }
    $triggers = func_get_arg( 1 ) ?? [];
    settype( $triggers, 'array' );

    $triggerList = $this->_observers->offsetGet( $observer );
    foreach ( $triggers as $trigger ) {
      if ( $triggerList->offsetExists( $trigger ) ) {
        $triggerList->offsetUnset( $trigger );
      }
    }

    if ( count( $triggers ) === 0 || $triggerList->count() === 0 ) {
      $this->_observers->offsetUnset( $observer );
    }
  }

  /**
   * @see api\Observable */
  public function notify() {
    $args = func_get_args();
    $event = array_shift( $args ) ?? 'update';
    foreach ( $this->_getObservers( $event ) as $observer ) {
      $observer->update( $this, $event, ...$args );
    }
  }

  /**
   * gets registered observers, filtered by event name.
   *
   * @param string $event  the event name to filter by
   * @return array         list of observers
   */
  protected function _getObservers( string $event ) {
    $observers = [];
    foreach ( $this->_observers as $observer => $triggerList ) {
      foreach ( $triggerList as $trigger ) {
        if ( preg_match( $trigger, $event ) === 1 ) {
          $observers[] = $observer;
          break;
        }
      }
    }
    return $observers;
  }
}
