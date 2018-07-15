<?php
/**
 * @package    at.util
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

namespace at\util;

use at\util\Json;

/**
 * provides implementation for json-serializing iterable objects.
 */
trait JsonableIterator {

  /**
   * @see https://php.net/jsonSerializable.jsonSerialize
   */
  public function jsonSerialize() : array {
    return $this->toArray();
  }

  /**
   * {@inheritDoc}
   */
  public function toArray(array $options = []) : array {
    return iterator_to_array($this);
  }

  /**
   * {@inheritDoc}
   */
  public function toJson(array $options = []) : string {
    return Json::encode($this);
  }
}
