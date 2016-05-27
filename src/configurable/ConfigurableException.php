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

use at\util\configurable\api\Configurable,
    at\util\exceptable\api\Exceptable as ExceptableApi,
    at\util\exceptable\exceptable;

class ConfigurableException extends \RuntimeException implements ExceptableApi {
  use exceptable;

  /**
   * Exception codes known to this class.
   *
   * @type int INVALID_CONFIG        invalid configuration
   * @type int INVALID_ADD_LIST      invalid "add{option}" setting
   * @type int INVALID_ARG_LIST      invalid argument list
   * @type int CONFIG_LOCKED         configuration is locked
   * @type int INVALID_CONFIG_CLASS  configuration classname is invalid
   */
  const INVALID_CONFIG = 1;
  const INVALID_ADD_LIST = (1<<1);
  const INVALID_ARG_LIST = (1<<2);
  const CONFIG_LOCKED = (1<<3);
  const INVALID_CONFIG_CLASS = (1<<4);

  /**
   * @see exceptable::DEFAULT_CODE */
  const DEFAULT_CODE = self::INVALID_CONFIG;

  /**
   * @see exceptable::INFO */
  const INFO = [
    self::INVALID_CONFIG => [
      'code' => self::INVALID_CONFIG,
      'message' => 'invalid configuration',
      'severity' => E_WARNING
    ],
    self::INVALID_ADD_LIST => [
      'code' => self::INVALID_ADD_LIST,
      'message' => 'option setting must be an array',
      'severity' => E_WARNING,
      'tr' => ['option' => '', 'type' => ''],
      'tr_message' => '"%option%" setting must be an array; [%type%] provided'
    ],
    self::INVALID_ARG_LIST => [
      'code' => self::INVALID_ARG_LIST,
      'message' => 'argument list must be an array',
      'severity' => E_WARNING,
      'tr' => ['option' => '', 'type' => ''],
      'tr_message' => 'argument list for "%option%" must be an array; [%type%] provided'
    ],
    self::CONFIG_LOCKED => [
      'code' => self::CONFIG_LOCKED,
      'message' => 'cannot write option (configuration is locked)',
      'severity' => E_NOTICE
    ]
  ];
}
