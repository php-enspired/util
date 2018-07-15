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

use DatePeriod,
    DateTimeImmutable,
    DateTimeInterface,
    DateTimeZone,
    IntlDateFormatter;

use at\util\DateTimeException;

/**
 * decorates PHP's DateTime.
 */
class DateTime extends DateTimeImmutable {

  /**
   * common timestrings.
   *
   * @type string NOW
   * @type string TODAY
   * @type string NOON
   * @type string TOMORROW
   * @type string YESTERDAY
   */
  const NOW = 'now';
  const TODAY = 'today';
  const NOON = 'noon';
  const TOMORROW = 'tomorrow';
  const YESTERDAY = 'yesterday';

  /**
   * formatting constants.
   *
   * @todo audit/remove where able once php 7.1 support is dropped
   *  (many formatting constants became available on DateTimeImmutable in 7.2)
   *
   * @type string COOKIE
   * @type string ISO8601
   * @type string W3C
   */
  const COOKIE = DATE_COOKIE;
  const ISO8601 = DATE_ATOM;  // @see https://php.net/class.datetime#datetime.constants.iso8601
  const W3C = DATE_W3C;

  /**
   * locale-aware formatting constants.
   * @see https://php.net/IntlDateFormatter
   *
   * @type int NONE    omit
   * @type int SHORT   e.g., 12/13/52 or 3:30pm
   * @type int MEDIUM  e.g., Jan 12, 1952
   * @type int LONG    e.g., January 12, 1952 or 3:30:32pm
   * @type int FULL    e.g., Tuesday, April 12, 1952 AD or 3:30:42pm PST
   */
  const NONE = IntlDateFormatter::NONE;
  const SHORT = IntlDateFormatter::SHORT;
  const MEDIUM = IntlDateFormatter::MEDIUM;
  const LONG = IntlDateFormatter::LONG;
  const FULL = IntlDateFormatter::FULL;

  /** @type string UTC timezone identifier. */
  const UTC = 'UTC';

  /**
   * @type string $_defaultFormat  default format for __toString.
   * @type string $_defaultLocale  default locale for __toString.
   */
  protected $_defaultFormat = self::ISO8601;
  protected $_defaultLocale = null;

  /**
   * convenience factory method.
   * @see DateTime::__construct()
   *
   * @param string $time        datetime string
   * @param mixed  $timezone    datetimezone string or instance
   * @return DateTimeInterface  a new DateTime instance
   */
  public static function create($time = self::NOW, $timezone = self::UTC) : DateTimeInterface {
    if (is_string($timezone)) {
      $timezone = new DateTimeZone($timezone);
    }

    return new self($time, $timezone);
  }

  /**
   * creates a datetime instance from a unix timestamp.
   *
   * @param mixed $time         a unix timestamp, as an integer, float, or string (no "@" prefix)
   * @throws DateTimeException  if time value cannot be interpreted as a unix timestamp
   * @return DateTimeInterface  a new DateTimeInterface instance
   */
  public static function createFromUnixtime($time) : DateTimeInterface {
    if (filter_var($time, FILTER_VALIDATE_FLOAT) === false) {
      throw new DateTimeException(DateTimeException::INVALID_UNIXTIME, ['time' => $time]);
    }

    if (strpos($time, '.') === false) {
      $time .= '.0';
    }

    return self::createFromFormat('U.u', $time);
  }

  /**
   * @see https://php.net/__toString
   */
  public function __toString() {
    return $this->format($this->_defaultFormat, $this->_defaultLocale);
  }

  /**
   * locale-aware formatter.
   *
   * if a locale is provided, the format must be one of:
   *  - locale-aware formatting constant (applied to both date and time parts)
   *  - array of locale-aware formatting constants for date and time parts
   *  - ICU datetime formatting string
   *
   * otherwise, format must be a DateTime formatting string.
   *
   * @param mixed       $format  the desired format
   * @param string|bool $locale  the desired ICU locale; or true to use default locale
   * @throws DateTimeException   if formatting fails
   * @return string              the formatted date/time
   */
  public function format($format = null, $locale = null) : string {
    if ($locale) {
      if (! class_exists(IntlDateFormatter::class)) {
        throw new DateTimeException(DateTimeException::INTL_NOT_AVAILABLE);
      }
      if ($locale === true) {
        if (! $this->_defaultLocale) {
          throw new DateTimeException(DateTimeException::NO_DEFAULT_LOCALE);
        }
        $locale = $this->_defaultLocale;
      }
      Value::hint('locale', $locale, Value::STRING, Value::NULL);

      $formatted = IntlDateFormatter::formatObject($this, $format, $locale);

      if ($formatted === false) {
        throw new DateTimeException(
          DateTimeException::INVALID_LOCALE_OR_FORMAT,
          ['format' => $format, 'locale' => $locale]
        );
      }
      return $formatted;
    }

    return parent::format($format);
  }

  /**
   * sets default locale to be used by format().
   *
   * note, the locale is not validated before its first use.
   *
   * @param string $locale  the locale to use (e.g., "en_US")
   * @return $this
   */
  public function setLocale(string $locale) : DateTimeInterface {
    $this->_defaultLocale = $locale;
    return $this;
  }

  /**
   * sets the format used by __toString().
   *
   * @param mixed $format  the format to use (see format())
   * @return $this
   */
  public function setToStringFormat($format) : DateTimeInterface {
    $this->_toStringFormat = $format;
    return $this;
  }
}
