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

use JsonSerializable;

/**
 * augmented interface for json-serializable objects.
 */
interface Jsonable extends JsonSerializable {

  /**
   * provides an array representation of the Jsonable instance.
   *
   * implmentations may use provided options to filter/augment the contents of the returned array,
   * which means the returned array may or may not be suitable for use with fromArray().
   * if options are omitted, the returned array *must* be a suitable $definition.
   *
   * without arguments, this method and jsonSerializable() *should* return the same array.
   *
   * @param array $options  options (implementation-defined)
   * @return array
   */
  public function toArray(array $options = []) : array;

  /**
   * provides a json representation of the Rule instance.
   *
   * this method *must* return the same array that toArray() returns, serialized as json.
   *
   * @param array $options  options (implementation-defined)
   * @return string
   */
  public function toJson(array $options = []) : string;
}
