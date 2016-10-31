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

use at\util\exceptable\api\Exceptable as ExceptableApi,
    at\util\exceptable\exceptable;

use RuntimeException;

/**
 * configuration-related exceptions.
 */
class ConfigurationException extends RuntimeException implements ExceptableApi {
  use exceptable;

  /**
   * exception codes known to this class.
   *
   * @type int INVALID_CONFIG        invalid configuration (default case)
   * @type int INVALID_ADD_LIST      invalid "add{option}" setting
   * @type int INVALID_ARG_LIST      invalid argument list
   * @type int CONFIG_LOCKED         configuration is locked
   * @type int INVALID_CONFIG_CLASS  configuration classname is invalid
   * @type int OPTION_ALREADY_SET    option already set and cannot be overwritten
   * @type int OPTION_DISALLOWED     setting option is disallowed (e.g., option name is nonpublic)
   */
  const INVALID_CONFIG = ExceptableApi::DEFAULT_CODE;
  const INVALID_ADD_LIST = 1;
  const INVALID_ARG_LIST = (1<<1);
  const CONFIG_LOCKED = (1<<2);
  const OPTION_ALREADY_SET = (1<<3);
  const OPTION_DISALLOWED = (1<<4);
  const INVALID_CONFIG_CLASS = (1<<5);

  /**
   * @see exceptable::INFO */
  const INFO = [
    self::INVALID_CONFIG => [
      'message' => 'invalid configuration',
      'severity' => E_WARNING
    ],
    self::INVALID_ADD_LIST => [
      'message' => 'option value must be an array',
      'severity' => E_WARNING,
      'tr' => ['option' => null, 'type' => null],
      'tr_message' => '"%option%" value must be an array; [%type%] provided'
    ],
    self::INVALID_ARG_LIST => [
      'message' => 'argument list must be an array',
      'severity' => E_WARNING,
      'tr' => ['option' => null, 'type' => null],
      'tr_message' => 'argument list for "%option%" must be an array; [%type%] provided'
    ],
    self::CONFIG_LOCKED => [
      'message' => 'cannot set option (configuration is locked)',
      'severity' => E_NOTICE
    ],
    self::OPTION_ALREADY_SET => [
      'message' => 'cannot set option (already set)',
      'severity' => E_NOTICE,
      'tr' => ['option' => null],
      'tr_message' => 'cannot set option "%option%" (already set)'
    ],
    self::OPTION_DISALLOWED => [
      'message' => 'cannot set option (disallowed)',
      'severity' => E_NOTICE,
      'tr' => ['option' => null],
      'tr_message' => 'cannot set option "%option%" (disallowed)'
    ],
    self::INVALID_CONFIG_CLASS => [
      'message' => 'configuration class must extend Ds\\Map',
      'severity' => E_ERROR,
      'tr' => ['class' => null],
      'tr_message' => 'configuration class must extend Ds\\Map; [%class%] provided'
    ]
  ];
}
