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
namespace at\util\configurable;

use at\util\configurable\ConfigurationException;

use Ds\Map;

/**
 * default implementation for Configurable interface.
 *
 * provides a constructor which accepts a map of configuration options.
 *
 * for any option where the implementation defines a corresponding "add{option}" method,
 * that option's value should be an array of argument lists for that "add{option}" method.
 *
 * likewise, where the implementation defines a "set{option}" method,
 * that option's value should be an argument list for that "set{option}" method.
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
  public function __construct(array $options=[]) {
    $this->_configuration = $this->_buildConfiguration()->merge($options);
    $this->_autoConfigure();
  }

  /**
   * @see Configurable::config() */
  public function config(string $setting) {
    return $this->_configuration->get($setting, null);
  }

  /**
   * @see Configurable::lockConfig() */
  public function lockConfig() {
    $this->_configuration->put('__locked__', true);
  }

  /**
   * @see Configurable::setConfig() */
  public function setConfig(string $setting, $value, bool $replace=false) {
    if ($this->_config('__locked__')) {
      throw new ConfigurationException(ConfigurationException::CONFIG_LOCKED);
    }

    if (strpos($setting, '__') === 0) {
      throw new ConfigurationException(
        ConfigurationException::OPTION_DISALLOWED,
        ['option' => $setting]
      );
    }

    $option = $this->config($setting);
    $isArray = is_array($option);

    if ($option !== null && ! ($replace || $isArray)) {
      throw new ConfigurationException(
        ConfigurationException::OPTION_ALREADY_SET,
        ['option' => $setting]
      );
    }

    if ($isArray && ! $replace) {
      $value = array_merge($option, (array) $value);
    }
    $this->_configuration->put($setting, $value);
  }

  /**
   * Looks for config options with matching add/set methods and invokes them.
   *
   * @throws ConfigurationException  if any config option is found to be invalid
   */
  protected function _autoConfigure() {
    try {
      $autoAdds = $this->_getAutoAdds();
      foreach ($autoAdds as $addMethod => $addlist) {
        foreach ($addlist as $add) {
          $this->{"add{$method}"}(...$add);
        }
      }
      foreach (array_diff($this->_getAutoSets(), $autoAdds) as $method => $set) {
        $this->{"set{$method}"}(...$set);
      }
    } catch (\Throwable $e) {
      throw new ConfigurationException(ConfigurationException::INVALID_CONFIG, $e);
    }
  }

  /**
   * ensures that the given list is a list of arg lists (an array of arrays).
   *
   * @throws ConfigurationException  if add list is malformed
   */
  protected function _assertAutoAddList($list, $option) {
    if (! is_array($list)) {
      throw new ConfigurationException(
        ConfigurationException::INVALID_ADD_LIST,
        ['option' => $option, 'type' => gettype($list)]
      );
    }

    foreach ($list as $add) {
      $this->_assertAutoArgs($add, $option);
    }
  }

  /**
   * ensures that the given list is an arg list (an array).
   *
   * @throws ConfigurationException  if arg list is malformed
   */
  protected function _assertAutoArgs($args, $option) {
    if (! is_array($args)) {
      throw new ConfigurationException(
        ConfigurationException::INVALID_ARG_LIST,
        ['option' => $option, 'type' => gettype($args)]
      );
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

  /**
   * finds config options with matching add* methods.
   *
   * @return array[][]  list of arg lists for config items to auto-add
   */
  protected function _getAutoAdds() : array {
    return array_filter(
      $this->_configuration,
      function($option, $addlist) {
        return method_exists($this, "add{$option}")
          && $this->_assertAutoAddList($addlist, $option);
      },
      ARRAY_FILTER_USE_BOTH
    );
  }

  /**
   * finds config options with matching set* methods.
   * N.B., this does not filter out options which also match add* methods.
   *
   * @return array[]  list of config items to auto-set
   */
  protected function _getAutoSets() : array {
    return array_filter(
      $this->_configuration,
      function($option, $set) {
        return method_exists($this, "set{$option}") && $this->_assertAutoArgs($set, $option);
      },
      ARRAY_FILTER_USE_BOTH
    );
  }
}
