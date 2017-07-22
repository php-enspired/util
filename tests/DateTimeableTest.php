<?php
/**
 * @package    at.util.tests
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

namespace at\util\tests;

use DateTime as BaseDateTime,
    DateTimeImmutable as BaseDateTimeImmutable,
    DateTimeZone;

use at\util\ {
  DateTime,
  DateTimeImmutable
};

use PHPUnit\Framework\TestCase;

class DateTimeableTest extends TestCase {

  /**
   * @covers DateTimeable::create
   * @covers DateTimeable::__construct
   * @dataProvider _timeableProvider
   */
  public function testCreate(array $time, $expected) {
    date_default_timezone_set('UTC');
    list($timeable, $timezone) = $time;

    $datetime = DateTime::create($timeable, $timezone);
    $datetimeimmutable = DateTimeImmutable::create($timeable, $timezone);

    $this->assertTrue($datetime instanceof BaseDateTime);
    $this->assertTrue($datetimeimmutable instanceof BaseDateTimeImmutable);

    $this->assertEquals($expected, $datetime);
    $this->assertEquals($expected, $datetimeimmutable);
  }

  /**
   * @return array[] {
   *    @type array $0  {
   *      @type string|int|float $0  a datetimeable value
   *      @type DateTimeZone     $1  a DateTimeZone instance
   *    }
   *    @type DateTimeInterface $1  a DateTime instance that the test is expected to match
   *  }
   */
  public function _timeableProvider() : array {
    $then = '20 July 1969';
    $yesterday = 'yesterday';
    $now = 'now';
    $tomorrow = 'tomorrow';
    $later = '28 July 2061';

    $thenDT = new BaseDateTimeImmutable('20 July 1969');
    $yesterdayDT = new DateTimeImmutable('yesterday');
    $nowDT = new BaseDateTimeImmutable('now');
    $tomorrowDT = new DateTimeImmutable('tomorrow');
    $laterDT = new BaseDateTimeImmutable('28 July 2061');

    $Z = new DateTimeZone('EST');
    $thenDTZ = new BaseDateTimeImmutable('20 July 1969', $Z);
    $yesterdayDTZ = new DateTimeImmutable('yesterday', $Z);
    $nowDTZ = new BaseDateTimeImmutable('now', $Z);
    $tomorrowDTZ = new DateTimeImmutable('tomorrow', $Z);
    $laterDTZ = new BaseDateTimeImmutable('28 July 2061', $Z);

    return [
      [[$then, null], $thenDT],
      [[intval($thenDT->format('U')), null], $thenDT],
      [[floatval($thenDT->format('U')), null], $thenDT],

      [[$then, $Z], $thenDTZ],
      [[intval($thenDTZ->format('U')), $Z], $thenDTZ],
      [[floatval($thenDTZ->format('U')), $Z], $thenDTZ],

      [[$yesterday, null], $yesterdayDT],
      [[intval($yesterdayDT->format('U')), null], $yesterdayDT],
      [[floatval($yesterdayDT->format('U')), null], $yesterdayDT],

      [[$yesterday, $Z], $yesterdayDTZ],
      [[intval($yesterdayDTZ->format('U')), $Z], $yesterdayDTZ],
      [[floatval($yesterdayDTZ->format('U')), $Z], $yesterdayDTZ],

      [[$now, null], $nowDT],
      [[intval($nowDT->format('U')), null], $nowDT],
      [[floatval($nowDT->format('U')), null], $nowDT],

      [[$now, $Z], $nowDTZ],
      [[intval($nowDTZ->format('U')), $Z], $nowDTZ],
      [[floatval($nowDTZ->format('U')), $Z], $nowDTZ],

      [[$tomorrow, null], $tomorrowDT],
      [[intval($tomorrowDT->format('U')), null], $tomorrowDT],
      [[floatval($tomorrowDT->format('U')), null], $tomorrowDT],

      [[$tomorrow, $Z], $tomorrowDTZ],
      [[intval($tomorrowDTZ->format('U')), $Z], $tomorrowDTZ],
      [[floatval($tomorrowDTZ->format('U')), $Z], $tomorrowDTZ],

      [[$later, null], $laterDT],
      [[intval($laterDT->format('U')), null], $laterDT],
      [[floatval($laterDT->format('U')), null], $laterDT],

      [[$later, $Z], $laterDTZ],
      [[intval($laterDTZ->format('U')), $Z], $laterDTZ],
      [[floatval($laterDTZ->format('U')), $Z], $laterDTZ]
    ];
  }
}
