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
namespace at\app\registrar;

use at\util\Vars;

/**
 * generic implementation of the Registrar interface. */
trait registrar {

  /**
   * @see Registrar::isRegisterable() */
  abstract public function isRegisterable($item) : bool;

  /**
   * @type array  name=>item map. */
  protected $_register = [];

  /**
   * @see Registrar::add() */
  public function add(string $name, $item) {
    if ($this->has($name)) {
      $m = "the name '{$name}' is already registered";
      throw new \OverflowException($m, E_WARNING);
    }
    if (! $this->isRegisterable($item)) {
      $t = Vars::type($item);
      $m = "\$item [{$t}] is not a registerable item";
      throw new \UnexpectedValueException($m, E_WARNING);
    }

    $this->_register[$name] = $item;
  }

  /**
   * @see Registrar::drop() */
  public function drop(string $name) {
    if (! $this->has($name)) {
      $m = "no item is registered under '{$name}'";
      throw new \UnderflowException($m, E_WARNING);
    }

    unset($this->_register[$name]);
  }

  /**
   * @see Registrar::has() */
  public function has(string $name) : bool {
    return isset($this->_register[$name]);
  }
}
