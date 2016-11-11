<?php
/**
 * @package    at.app
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
namespace at\app\registrar\api;

/**
 * interface for objects which maintain registries of items.
 */
interface Registrar {

  /**
   * registers an item.
   *
   * @param string $name               the name to register
   * @param mixed  $item               the item to register
   * @throws OverflowException         if the given name is already registered
   * @throws UnexpectedValueException  if the given item cannot be registered
   */
  public function add(string $name, $item);

  /**
   * unregisters an item.
   *
   * @param string   $name       the name to register
   * @throws UnderflowException  if no item is registered under the given name
   */
  public function drop(string $name);

  /**
   * checks if an item is registered under a given name.
   *
   * @param string $name  the name to check
   * @return bool         true if an item is registered under the given name; false otherwise
   */
  public function has(string $name) : bool;

  /**
   * checks whether a given item can be registered.
   *
   * @param mixed $item  the item to examine
   * @return bool        true if the item can be added to the registry; false otherwise
   */
  public function isRegisterable($item) : bool;
}
