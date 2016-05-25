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
 * base implementation of Observable (SplSubject). */
trait observable {

  /**
   * @type Map  observer => trigger list map */
  protected $_observers;

  /**
   * trait constructor. */
  public function __construct() {
    $this->_observers = new Map();
  }

  /**
   * @see api\Observable */
  public function attach( \SplObserver $observer ) {
    $triggers = array_map(
      function( $trigger ) { return new Trigger( $trigger ); },
      ((array) (func_get_arg( 1 ) ?: null))
    );
    $append = ((bool) (func_get_arg( 2 ) ?: true));

    $triggerList = ($append && $this->_observers->hasKey( $observer )) ?
      $this->_observers->get( $observer ):
      new Set;
    $triggerList->add( ...$triggers );
    $this->_observers->put( $observer, $triggerList );
  }

  /**
   * @see api\Observable */
  public function detach( \SplObserver $observer ) {
    if ( ! $this->_observers->hasKey( $observer ) ) {
      return;
    }
    $triggers = array_map(
      function( $trigger ) { return new Trigger( $trigger ); },
      ((array) (func_get_arg( 1 ) ?: []))
    );

    $triggerList = $this->_observers->get( $observer );
    $triggerList->remove( ...$triggers );

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
        if ( $trigger->matches( $event ) ) {
          $observers[] = $observer;
          break;
        }
      }
    }
    return $observers;
  }
}
