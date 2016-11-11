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
namespace at\accessible\api;

/**
 * interface for objects which present access to a dataset.
 *
 * note; though this interface defines methods similar to (and compatible with) ArrayAccess,
 * it does not extend that interface.
 */
interface Accessible {

  /**
   * checks whether the given offset exists in (can be read from) the dataset.
   *
   * @param mixed $offset  the offset to check
   * @return bool          true if offset exists; false otherwise
   */
  public function offsetExists($offset) : bool;

  /**
   * gets the value at a given offset.
   *
   * @param mixed $offset         the offset to get
   * @throws AccessibleException  if the offset does not exist in the dataset
   * @return mixed                the value of the given offset on success
   */
  public function offsetGet($offset);

  /**
   * gets list of offsets in the dataset.
   *
   * @return string[]  list of offset names
   */
  abstract public function offsets() : array;

  /**
   * assigns a value to the given offset.
   * note; whether this method can create new offsets is implementation-dependent.
   *
   * @param mixed $offset         the offset to set
   * @param mixed $value          the value to assign
   * @throws AccessibleException  if the offset cannot be set
   */
  public function offsetSet($offset, $value);

  /**
   * removes an offset.
   * note; whether this method removes actual offsets (as opposed to removing their value)
   * is implementation-dependent.
   *
   * @param mixed $offset         the offset to remove
   * @throws AccessibleException  if the offset cannot be unset
   */
  public function offsetUnset($offset);

  /**
   * checks whether a value is valid for (can be written to) a given offset.
   *
   * @param mixed $offset  the offset to set
   * @param mixed $value   the value to assign
   * @return bool          true if the value is valid for the given offset; false otherwise
   */
  public function offsetValid($offset, $value) : bool;
}
