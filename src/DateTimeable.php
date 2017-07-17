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

use DateTime as BaseDateTime,
    DateTimeImmutable as BaseDateTimeImmutable,
    DateTimeInterface,
    DateTimeZone;

/**
 * convenience methods for PHP's built-in DateTime classes.
 */
trait DateTimeable {

  /**
   * convenience factory method.
   * @see DateTimeable::__construct()
   *
   * @return DateTimeInterface  a new DateTimeInterface instance
   */
  public static function create($time, $timezone = null) : DateTimeInterface {
    return new self($time, $timezone);
  }

  /**
   * interprets integers/strings of digits as unix timestamps.
   *
   * @param mixed $time      unix timestamp or datetime string
   * @param mixed $timezone  datetimezone string or instance
   */
  public function __construct($time, $timezone = null) {
    parent::__construct(
      is_int(filter_var($time, FILTER_VALIDATE_INT)) ? "@{$time}" : $time,
      is_string($timezone) ? new DateTimeZone($timezone) : $timezone
    );
  }
}

class DateTime extends BaseDateTime {
  use DateTimeable;
}

class DateTimeImmutable extends BaseDateTimeImmutable {
  use DateTimeable;
}
