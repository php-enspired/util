<?php
/**
 * @package    at.util
 * @version    0.4
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GPL-3.0 (only)
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
namespace at\observable;

use at\observable\ObservableException,
    at\observable\Trigger;
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
  public function attach(\SplObserver $observer) {
    if (! $this->_observers->hasKey($observer)) {
      $this->_observers->put($observer, (new Trigger));
    }
    $patterns = (array) (func_get_arg(1) ?: [Trigger::DEFAULT_PATTERN]);
    $trigger = $this->_observers->get($observer);
    // clear existing trigger patterns?
    if (func_get_arg(2)) {
      $trigger->clear();
    }
    $trigger->add(...$patterns);
  }

  /**
   * @see api\Observable */
  public function detach(\SplObserver $observer) {
    if (! $this->_observers->hasKey($observer)) {
      return;
    }
    $triggers = func_get_arg(1);
    if ($triggers) {
      $this->_observers->get($observer)->remove(...$triggers);
    } else {
      $this->_observers->remove($observer);
    }
  }

  /**
   * @see api\Observable */
  public function notify() {
    $args = func_get_args();
    $event = array_shift($args) ?? 'update';
    foreach ($this->_getObservers($event) as $observer) {
      $observer->update($this, $event, ...$args);
    }
  }

  /**
   * gets registered observers, filtered by event name.
   *
   * @param string $event  the event name to filter by
   * @return array         list of observers
   */
  protected function _getObservers(string $event) {
    $observers = [];
    foreach ($this->_observers as $observer => $trigger) {
      if ($trigger->matches($event)) {
        $observers[] = $observer;
        break;
      }
    }
    return $observers;
  }
}
