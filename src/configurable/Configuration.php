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
namespace at\util\configurable;

use at\util\accessible\accessible,
    at\util\configurable\api\Configuration as ConfigurationApi,
    at\util\enumerable\enumerable,
    at\util\Arrays;

/**
 * interface for configuration objects.
 *
 * @method bool  accessible::offsetExists( mixed $offset )
 * @method mixed accessible::offsetGet( mixed $offset )
 * @method void  accessible::offsetSet( mixed $offset, mixed $value )
 * @method void  accessible::offsetUnset( mixed $offset )
 * @method void  accessible::offsetValid( mixed $offset, mixed $value )
 *
 * @method mixed  enumerable::current( void )
 * @method scalar enumerable::key( void )
 * @method void   enumerable::next( void )
 * @method void   enumerable::rewind( void )
 * @method bool   enumerable::valid( void )
 */
class Configuration implements ConfigurationApi {
  use accessible, enumerable {
    accessible::offsetSet   as protected _offsetSet;
    accessible::offsetUnset as protected _offsetUnset;
  }

  /**
   * @type bool  is this instance locked (read-only)? */
  protected $_locked = false;

  /**
   * @param array $settings         setting => runtime value map of configuration options
   * @throws ConfigurableException  if any configuration option is invalid
   */
  public function __construct( array $settings=[] ) {
    try {
      $settings = Arrays::extend( $this->_defaults(), $settings );
      foreach ( $settings as $setting=>$value ) {
        $this->offsetSet( $setting, $value );
      }
    } catch ( \Throwable $e ) {
      throw new ConfigurableException( ConfigurableException::INVALID_CONFIG, $e );
    }
  }

  /**
   * @see ConfigurationApi::isLocked() */
  public function isLocked() : bool {
    return $this->_locked;
  }

  /**
   * @see ConfigurationApi::lock() */
  public function lock() {
    $this->_locked = true;
  }

  /**
   * @see accessible::offsetSet() */
  public function offsetSet( $offset, $value ) {
    if ( $this->_locked ) {
      throw new ConfigurableException( ConfigurableException::CONFIG_LOCKED );
    }
    $this->_offsetSet( $offset, $value );
  }

  /**
   * @see accessible::offsetUnset() */
  public function offsetUnset( $offset ) {
    if ( $this->_locked ) {
      throw new ConfigurableException( ConfigurableException::CONFIG_LOCKED );
    }
    $this->_offsetUnset( $offset );
  }

  /**
   * gets the default settings for this configuration.
   * overriding this method is probably the only legitimate reason to extend this class.
   *
   * @return array  setting => default value map
   */
  protected function _defaults() : array {
    return [];
  }
}
