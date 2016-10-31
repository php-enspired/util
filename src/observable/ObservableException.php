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
namespace at\util\exception;

use at\mixin\api\Exceptable as ExceptableAPI,
    at\mixin\exceptable;

/**
 * exceptions known to the observable/observer implementations. */
class ObservableException extends \RuntimeException implements ExceptableAPI {
  use exceptable;

  /**
   * @type int DEFAULT_CODE
   * @type int INVALID_TRIGGER
   * @type int WRONG_ON_ARGS       invalid
   * @type int NO_TRIGGERS         no triggers provided for a handler
   * @type int TYPEERROR_OFF       wrong args for observer::off
   * @type int UNCAUGHT_EXCEPTION  uncaught exception during observer::update
   * @type int TYPEERROR_ON        wrong args for observer::on
   */
  const DEFAULT_CODE = 0;
  const INVALID_TRIGGER = (1<<1);
  const WRONG_ON_ARGS = (1<<2);
  const NO_TRIGGERS = (1<<3);
  const TYPEERROR_OFF = (1<<4);
  const UNCAUGHT_EXCEPTION = (1<<5);
  const TYPEERROR_ON = (1<<6);

  /**
   * @type array  info about exception cases known to this exception class
   *
   * N.B., runtime messages are set via json_last_error_msg(),
   * and may or may not match the messages given here.
   */
  const INFO = [
    self::DEFAULT_CODE => [
      'code' => self::DEFAULT_CODE,
      'message' => 'unknown error',
      'severity' => 0
    ],
    self::INVALID_TRIGGER => [
      'code' => self::INVALID_TRIGGER,
      'message' => 'each $trigger must be a string',
      'severity' => E_WARNING,
      'tr_message' => 'each $trigger must be a string; [%type%] provided',
      'tr' => ['type' => 'unknown type']
    ],
    self::WRONG_ON_ARGS => [
      'code' => self::WRONG_ON_ARGS,
      'message' => 'each item in $events must be a trigger=>handler pair',
      'severity' => E_WARNING
    ],
    self::NO_TRIGGERS => [
      'code' => self::NO_TRIGGERS,
      'message' => 'no',
      'severity' => E_WARNING
    ],
    self::TYPEERROR_OFF => [
      'code' => self::TYPEERROR_OFF,
      'message' => 'observer::off() expects at least one argument; none given',
      'severity' => E_WARNING,
      'tr_message' => '%method%() expects at least one argument; none given',
      'tr' => ['method' => 'observer::off']
    ],
    self::UNCAUGHT_EXCEPTION => [
      'code' => self::UNCAUGHT_EXCEPTION,
      'message' => 'uncaught exception during update',
      'severity' => E_ERROR,
      'tr_message' => 'uncaught exception during update [%subject%::%event%]: [%message%]',
      'tr' => [
        'subject' => 'observer',
        'event' => 'update',
        'message' => 'unknown error'
      ]
    ],
    self::TYPEERROR_ON => [
      'code' => self::TYPEERROR_ON,
      'message' => 'observer::on expects $triggers to be an array',
      'severity' => E_WARNING,
      'tr_message' => '%method% expects $triggers to be an array; %type% given',
      'tr' => [
        'method' => 'observer::on',
        'type' => 'unknown type'
      ]
    ]
  ];
}
