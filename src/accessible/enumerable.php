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
namespace at\accessible;

/**
 * basic implementation of Enumerable.
 * intended for use with at\accessible\accessible.
 *
 * implementations may define enumerable offsets via setEnumerable().
 * otherwise, by default, all items in the dataset will be enumerated.
 */
trait enumerable {

  /**
   * @see Accessible::offsetExists() */
  abstract public function offsetExists($offset) : bool;

  /**
   * @see Accessible::offsetGet() */
  abstract public function offsetGet($offset);

  /**
   * @see Accessible::offsets() */
  abstract public function offsets() : array;

  /**
   * @see Accessible::offsetSet() */
  abstract public function offsetSet($offset, $value);

  /**
   * @see Accessible::offsetUnset() */
  abstract public function offsetUnset($offset);

  /**
   * @type array $_enumerable   list of enumerable offset names.
   * @type array $_enumeration  tracks position for the current enumeration.
   */
  protected $_enumerable;
  private $_enumeration = [];

  /**
   * @see Enumerable::count() */
  public function count() : int {
    if (empty($this->_enumeration)) {
      $this->rewind();
    }
    return count($this->_enumeration);
  }

  /**
   * @see Enumerable::current() */
  public function current() {
    return $this->offsetGet($this->key());
  }

  /**
   * @see Enumerable::key() */
  public function key() {
    return current($this->_enumeration);
  }

  /**
   * @see Enumerable::next() */
  public function next() {
    next($this->_enumeration);
  }

  /**
   * @see Enumerable::rewind() */
  public function rewind() {
    $this->_enumeration = is_array($this->_enumerable) ?
      $this->_enumerable :
      $this->_offsets();
  }

  /**
   * @see Enumerable::valid() */
  public function valid() : bool {
    $key = $this->key();
    return ($key !== null && $this->offsetExists($key));
  }

  /**
   * @see Enumerable::toArray() */
  public function toArray() : array {
    return iterator_to_array($this);
  }

  /**
   * makes offsets enumerable.
   *
   * @param string[] $offsets  the offsets to make enumerable (NULL = all offsets)
   */
  protected function _setEnumerable(array $offsets=null) {
    $this->_enumerable = $offsets;
  }
}
