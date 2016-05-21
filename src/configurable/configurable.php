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

use at\util\configurable\api\ConfigurationApi,
    at\util\configurable\Configuration,
    at\util\configurable\ConfigurationException;

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
   * @type Configuration $_configuration       configuration object for this instance
   * @type string        $_configurationClass  fqcn of Configuration class to use
   */
  protected $_configuration;
  protected $_configurationClass = Configuration::class;

  /**
   * trait constructor.
   *
   * @param array $options  map of configuration options
   */
  public function __construct( array $options=[] ) {
    $this->_configuration = $this->_buildConfiguration( $options );
    $this->_autoConfigure();
  }

  /**
   * @see Configurable::config() */
  public function config( string $setting ) {
    return $this->_configuration->offsetGet( $setting );
  }

  /**
   * Looks for config options with matching add/set methods and invokes them.
   *
   * @throws ConfigurationException  if any config option is found to be invalid
   */
  protected function _autoConfigure() {
    try {
      foreach ( $this->_configuration as $option=>$setting ) {
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
   * provides a configuration object using provided and/or default settings.
   *
   * @param array $settings  map of configuration options passed at runtime
   * @return Configuration
   */
  protected function _buildConfiguration( array $settings ) : ConfigurationApi {
    if ( ! is_a( $this->_configurationClass, ConfigurationApi::class, true ) ) {
      throw new ConfigurableException( ConfigurableException::INVALID_CONFIG_CLASS );
    }
    return new $this->_configurationClass( $settings );
  }
}
