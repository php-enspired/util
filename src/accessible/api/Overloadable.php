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
 * interface for objects which use property overloading to access a dataset. */
interface Overloadable extends Accessible {

  /**
   * aliases Accessible::offsetGet() */
  public function __get($name);

  /**
   * aliases Accessible::offsetExists() */
  public function __isset($name) : bool;

  /**
   * aliases Accessible::offsetSet() */
  public function __set($name, $value);

  /**
   * aliases Accessible::offsetUnset() */
  public function __unset($name);
}
