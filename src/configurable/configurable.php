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

use at\util\configurable\ConfigurationException;
use Ds\Map;

/**
 * default implementation for Configurable interface.
 *
 * accepts a map of runtime configuration options.
 *
 * for any option where the implementation defines a corresponding "add{option}" method,
 * that option will be treated as a list of argument lists for the "add{option}" method.
 *
 * likewise, where the implementation defines a "set{option}" method,
 * that method will be invoked using the option's value.
 */
trait configurable {

  /**
   * @type Map  configuration map for this instance. */
  protected $_configuration;

  /**
   * trait constructor.
   *
   * @param array $options  map of configuration options
   */
  public function __construct( array $options=[] ) {
    $this->_configuration = $this->_buildConfiguration()->merge( $options );
    $this->_autoConfigure();
  }

  /**
   * @see Configurable::config() */
  public function config( string $setting ) {
    return $this->_configuration->get( $setting, null );
  }

  /**
   * Looks for config options with matching add/set methods and invokes them.
   *
   * @throws ConfigurationException  if any config option is found to be invalid
   */
  protected function _autoConfigure() {
    try {
      foreach ( $this->_configuration as $option=>$setting ) {
        if ( ! is_string( $option ) ) {
          continue;
        }
        if ( method_exists( $this, "add{$option}" ) ) {
          if ( ! is_array( $setting ) ) {
            throw new ConfigurationException(
              ConfigurationException::INVALID_ADD_LIST,
              ['option' => $option, 'type' => gettype( $setting )]
            );
          }
          foreach ( $setting as $arguments ) {
            if ( ! is_array( $arguments ) ) {
              throw new ConfigurationException(
                ConfigurationException::INVALID_ARG_LIST,
                ['option' => $option, 'type' => gettype( $arguments )]
              );
            }
            $this->{"add{$option}"}( ...$arguments );
          }
        } elseif ( method_exists( $this, "set{$option}" ) ) {
          if ( ! is_array( $setting ) ) {
            throw new ConfigurationException(
              ConfigurationException::INVALID_ARG_LIST,
              ['option' => $option, 'type' => gettype( $setting )]
            );
          }
          $this->{"set{$option}"}( ...$setting );
        }
      }
    } catch ( \Throwable $e ) {
      throw new ConfigurationException( ConfigurationException::INVALID_CONFIG, $e );
    }
  }

  /**
   * provides a configuration object with default settings.
   *
   * @return Map
   */
  protected function _buildConfiguration() : Map {
    return new Map;
  }
}
