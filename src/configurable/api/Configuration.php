<?php
/**
 * @package    at.util
 * @version    0.4
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GPL-3.0 (no later versions permitted)
 *
 *  This program is free software: you can redistribute it and/or modify it
 *  under the terms of the GNU General Public License, version 3.
 *  You MAY NOT apply the terms of any later version of the GPL.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program.
 *  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 */
declare( strict_types = 1 );
namespace at\util\configurable\api;

use at\util\accessible\api\Accessible,
    at\util\enumerable\api\Enumerable;

/**
 * interface for configuration objects.
 *
 * @method bool  Accessible::offsetExists( mixed $offset )
 * @method mixed Accessible::offsetGet( mixed $offset )
 * @method void  Accessible::offsetSet( mixed $offset, mixed $value )
 * @method void  Accessible::offsetUnset( mixed $offset )
 * @method void  Accessible::offsetValid( mixed $offset, mixed $value )
 *
 * @method mixed  Enumerable::current( void )
 * @method scalar Enumerable::key( void )
 * @method void   Enumerable::next( void )
 * @method void   Enumerable::rewind( void )
 * @method bool   Enumerable::valid( void )
 */
interface Configuration extends Accessible, Enumerable {

  /**
   * makes the configuration instance immutable (read-only). */
  public function lock();
}
