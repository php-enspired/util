<?php
/**
 * @package    at.util
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

namespace at\util;

use at\exceptable\Exception as Exceptable;

/**
 * error cases for arraytool methods.
 */
class DateTimeException extends Exceptable {

  /**
   * @type int INVALID_UNIXTIME
   * @type int NO_DEFAULT_LOCALE
   */
  const INVALID_UNIXTIME = (1<<1);
  const NO_DEFAULT_LOCALE = (1<<2);
  const INVALID_LOCALE_OR_FORMAT = (1<<3);
  const INTL_NOT_AVAILABLE = (1<<4);

  /** @see Exceptable::INFO */
  const INFO = [
    self::INVALID_UNIXTIME => [
      'message' => 'invalid unixtime',
      'tr_message' => 'unixtime must be a number of seconds[.microseconds]; {time} provided'
    ],
    self::NO_DEFAULT_LOCALE => [
      'message' => 'no default locale is set',
      'severity' => E_NOTICE
    ],
    self::INVALID_LOCALE_OR_FORMAT => [
      'message' => 'invalid locale or format',
      'tr_message' => 'invalid locale or format; "{locale}", "{format}" provided',
      'severity' => E_NOTICE
    ],
    self::INTL_NOT_AVAILABLE => [
      'message' => 'IntlDateFormatter is not available; check that ext/intl is installed and loaded'
    ]
  ];
}
