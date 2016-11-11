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
 * basic Overloadable implementation.
 * intended for use with at\accessible\accessible.
 */
trait overloadable {

  /**
   * @see Accessible::offsetExists() */
  abstract public function offsetExists($offset) : bool;

  /**
   * @see Accessible::offsetGet() */
  abstract public function offsetGet($offset);

  /**
   * @see Accessible::offsetSet() */
  abstract public function offsetSet($offset, $value);

  /**
   * @see Accessible::offsetUnset() */
  abstract public function offsetUnset($offset);

  /**
   * @see Overloadable::__get() */
  public function __get($name) {
    return $this->offsetGet($name);
  }

  /**
   * @see Overloadable::__isset() */
  public function __isset($name) : bool {
    return $this->offsetExists($name);
  }

  /**
   * @see Overloadable::__set() */
  public function __set($name, $value) {
    $this->offsetSet($name, $value);
  }

  /**
   * @see Overloadable::__unset() */
  public function __unset($name) {
    $this->offsetUnset($name);
  }
}
