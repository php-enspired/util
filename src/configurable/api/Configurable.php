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
namespace at\configurable\api;

/**
 * interface for objects which accept runtime configurations. */
interface Configurable {

  /**
   * gets a configuration setting for a given key.
   *
   * @param string $setting  setting key
   * @return mixed|null      value of the configuration setting if exists; null otherwise
   */
  public function config(string $setting);

  /**
   * sets a configuration option.
   *
   * setting an option will fail if option already exists (and is not null),
   * unless $replace is true or the setting is an array (in which case, it will be merged).
   *
   * @param string $setting          setting key
   * @param mixed  $value            value to set
   * @param bool   $replace          replace existing value(s)?
   * @throws ConfigurationException  if setting cannot be set
   */
  public function setConfig(string $setting, $value, bool $replace=false);
}
