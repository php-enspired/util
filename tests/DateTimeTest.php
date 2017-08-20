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
    DateTimeInterface,
    DateTimeZone;

use at\util\DateTime;

use PHPUnit\Framework\TestCase;

class DateTimeTest extends TestCase {

  /**
   * @covers DateTime::create
   * @covers DateTime::__construct
   * @dataProvider _timeableProvider
   */
  public function testCreate(array $time, $expected) {
    date_default_timezone_set('UTC');
    list($timeable, $timezone) = $time;

    $datetime = DateTime::create($timeable, $timezone);
    $this->assertTrue($datetime instanceof DateTimeInterface);
    $this->assertEquals($expected, $datetime);
  }

  /**
   * @covers DateTime::createFromUnixtime
   * @covers DateTime::__construct
   * @dataProvider _unixtimeProvider
   */
  public function testCreateFromUnixtime($unixtime, $expected) {
    $datetime = DateTime::createFromUnixtime($unixtime);
    $this->assertTrue($datetime instanceof DateTimeInterface);
    $this->assertEquals($expected, $datetime);
  }

  /**
   * @covers DateTime::__toString
   * @covers DateTime::setToStringFormat
   */
  public function testSetToStringFormat() {
    $dt = new DateTime;

    $this->assertEquals($dt->format(DateTime::ISO8601), strval($dt));

    $dt->setToStringFormat(BaseDateTime::W3C);
    $this->assertEquals($dt->format(BaseDateTime::W3C), strval($dt));
  }

  /**
   * @todo fix intermittent failures due to invalid test examples
   * (DateTime instances being created on different seconds)
   *
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

    $thenDT = new BaseDateTime('20 July 1969');
    $yesterdayDT = new DateTime('yesterday');
    $nowDT = new BaseDateTime('now');
    $tomorrowDT = new DateTime('tomorrow');
    $laterDT = new BaseDateTime('28 July 2061');

    $Z = new DateTimeZone('EST');
    $thenDTZ = new BaseDateTime('20 July 1969', $Z);
    $yesterdayDTZ = new DateTime('yesterday', $Z);
    $nowDTZ = new BaseDateTime('now', $Z);
    $tomorrowDTZ = new DateTime('tomorrow', $Z);
    $laterDTZ = new BaseDateTime('28 July 2061', $Z);

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

  /**
   * @return array[] {
   *    @type mixed $0              unix timestamp as int, float, or string
   *    @type DateTimeInterface $1  expected DateTime instance
   *  }
   */
  public function _unixtimeProvider() : array {
    $dtU = new BaseDateTime('@' . time());
    $dtu = BaseDateTime::createFromFormat('U.u', (string) microtime(true));

    return [
      [$dtU->format('U'), $dtU],
      [$dtU->format('U.u'), $dtU],
      [intval($dtU->format('U.u')), $dtU],
      [floatval($dtU->format('U.u')), $dtU],

      [$dtu->format('U.u'), $dtu],
      [floatval($dtu->format('U.u')), $dtu]
    ];
  }
}
