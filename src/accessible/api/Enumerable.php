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
namespace at\util\accessible\api;

/**
 * interface for objects which enumerate over a dataset.
 */
interface Enumerable extends \Countable, \Iterator {

  /**
   * counts how many items are in the dataset.
   *
   * @return int  count of items in the dataset
   */
  public function count() : int;

  /**
   * gets the value at the current position in the dataset.
   *
   * @return mixed  the current value
   */
  public function current();

  /**
   * gets the key at the current position in the dataset.
   *
   * @return mixed  the current key
   */
  public function key();

  /**
   * moves to the next position in the dataset. */
  public function next();

  /**
   * moves to the first item in the dataset. */
  public function rewind();

  /**
   * gets the enumerable offsets of the dataset as an array.
   *
   * @return array  the dataset
   */
  public function toArray() : array;

  /**
   * checks whether the current position in the dataset is valid.
   *
   * @return bool  true if current position is valid; false otherwise
   */
  public function valid() : bool;
}
