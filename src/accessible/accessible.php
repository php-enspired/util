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

use at\accessible\AccessibleException,
    at\util\Vars;

/**
 * basic implementation of Accessible.
 *
 * implementations may define allowed offsets via setOffsets().
 * otherwise, by default, offsets may be added/removed from the dataset freely.
 *
 * supports custom property accessor methods:
 *  - @method mixed get{offset}(void)            reads a value for the offset
 *  - @method void  set{offset}(mixed $value)    writes a value for the offset
 *  - @method void  unset{offset}(void)          removes a value for the offset
 *  - @method bool  valid{offset}(mixed $value)  validates a value for the offset
 */
trait accessible {

  /**
   * @type array $_data     the dataset
   * @type array $_offsets  list of valid offset names for the dataset
   */
  protected $_data = [];
  protected $_offsets;

  /**
   * @see Accessible::offsetExists() */
  public function offsetExists($offset) : bool {
    if (! is_scalar($offset)) {
      return false;
    }
    return (method_exists($this, "get{$offset}") || in_array($offset, $this->offsets()));
  }

  /**
   * @see Accessible::offsetGet() */
  public function offsetGet($offset) {
    if (! $this->offsetExists($offset)) {
      throw new AccessibleException(
        AccessibleException::INVALID_OFFSET,
        ['offset' => $offset]
     );
    }
    return (method_exists($this, "get{$offset}")) ?
      $this->{"get{$offset}"}() :
      $this->_data[$offset];
  }

  /**
   * @see Accessible::offsets() */
  public function offsets() : array {
    return (is_array($this->_offsets)) ?
      $this->_offsets :
      array_keys($this->_data);
  }

  /**
   * @see Accessible::offsetSet() */
  public function offsetSet($offset, $value) {
    if (! $this->offsetValid($offset, $value)) {
      $code = ($this->offsetExists($offset)) ?
        AccessibleException::INVALID_VALUE :
        AccessibleException::INVALID_OFFSET;
      throw new AccessibleException(
        $code,
        ['offset' => $offset, 'value' => Vars::debug($value)]
     );
    }

    if (method_exists($this, "set{$offset}")) {
      $this->{"set{$offset}"}($value);
      return;
    }
    $this->_data[$offset] = $value;
  }

  /**
   * @see Accessible::offsetUnset() */
  public function offsetUnset($offset) {
    if (method_exists($this, "unset{$offset}")) {
      $this->{"unset{$offset}"}();
      return;
    }
    $this->offsetSet($offset, null);
  }

  /**
   * @see Accessible::offsetValid() */
  public function offsetValid($offset, $value) : bool {
    if (method_exists($this, "valid{$offset}")) {
      return $this->{"valid{$offset}"}($value);
    }
    return ($this->_offsets === null) ?
      is_scalar($offset) :
      $this->offsetExists($offset);
  }

  /**
   * sets offset names for the dataset.
   *
   * @param string[]|null $offsets  offset names for dataset (NULL = allow any offsets)
   */
  protected function _setOffsets(array $offsets=null) {
    $this->_offsets = $offsets;
  }
}
