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

use ArrayObject,
    DateTimeInterface,
    JsonSerializable,
    stdClass,
    Throwable;

use PHPUnit\Framework\TestCase;

use at\util\ {
  DateTime,
  Vars,
  VarsException
};

class VarsTest extends TestCase {

  /**
   * convenience function for setting exception expectations.
   *
   * @param string|Throwable $exception  the exception to expect
   */
  public function expectException($exception) {
    $instance = $exception instanceof Throwable;
    parent::expectException($instance ? get_class($exception) : $exception);
    if (! $instance) { return; }

    $code = $exception->getCode();
    if ($code) {
      $this->expectExceptionCode($code);
    }

    $message = $exception->getMessage();
    if ($message) {
      $this->expectExceptionMessage($message);
    }
  }

  /**
   * @covers Vars::isDateTimeable
   * @covers Vars::toDateTime
   * @dataProvider _dateTimeableProvider
   */
  public function testDateTime($datetimeable, $expected) {
    $isDateTimeable = $expected instanceof DateTimeInterface;
    if (! $isDateTimeable) {
      $this->expectException($expected);
    }

    $this->assertEquals($isDateTimeable, Vars::isDateTimeable($datetimeable));
    $this->assertEquals($expected, Vars::toDateTime($datetimeable));
  }

  /**
   * @covers Vars::debug
   */
  public function testDebug() {
    $this->assertEquals("string(3) \"foo\"\n", Vars::debug('foo'));
    $this->assertEquals("string(3) \"foo\"\nint(42)\n", Vars::debug('foo', 42));

    $this->expectException(new VarsException(VarsException::NO_EXPRESSIONS));
    Vars::debug();
  }

  /**
   * @covers Vars::filter
   * @dataProvider _filterProvider
   */
  public function testFilter(array $args, $expected = null) {
    if ($expected instanceof Throwable) {
      $this->expectException($expected);
    }

    $this->assertEquals($expected, Vars::filter(...$args));
  }

  /**
   * @covers Vars::isIterable
   */
  public function testIsIterable() {
    $this->assertTrue(Vars::isIterable(new ArrayObject));
    $this->assertTrue(Vars::isIterable([]));

    $this->assertFalse(Vars::isIterable(7));
    $this->assertFalse(Vars::isIterable('abc'));
  }

  /**
   * @covers Vars::isJsonable
   * @dataProvider _jsonableProvider
   */
  public function testIsJsonable($jsonable, bool $expected) {
    $this->assertEquals($expected, Vars::isJsonable($jsonable));
  }

  /**
   * @return array[] {
   *    @type mixed                  $0  a value
   *    @type DateTime|VarsException $1  the expected result
   *  }
   */
  public function _dateTimeableProvider() : array {
    $midnight = new DateTime('midnight');
    // we're not testing DateTime; only the behavior of is|toDateTime().
    // limiting to a few representative cases is fine.
    return [
      ['midnight', $midnight],
      [$midnight, $midnight],
      [(int) $midnight->format('U'), $midnight],
      [(float) $midnight->format('U.u'), $midnight],
      [$midnight->format('@U.u'), $midnight],

      [
        'train wreck',
        new VarsException(
          VarsException::UNCASTABLE,
          ['value' => 'train wreck', 'type' => 'DateTime']
        )
      ]
    ];
  }

  /**
   * @return array[] {
   *    @type mixed $0  args to pass to filter()
   *    @type mixed $1  the expected result
   *  }
   */
  public function _filterProvider() : array {

    // @todo
    $this->markTestIncomplete('not yet implemented');
    return [[]];

    return array_merge(
      $this->_boolProvider(),
      $this->_emailProvider(),
      $this->_floatProvider(),
      $this->_intProvider(),
      $this->_iterableProvider(),
      $this->_dateTimeableProvider(),
      $this->_callableProvider(),
      $this->_stringableProvider()
    );

    $emails = [
      ['用户@例子.广告', true],
      ['उपयोगकर्ता@उदाहरण.कॉम', true],
      ['юзер@екзампл.ком', true],
      ['θσερ@εχαμπλε.ψομ', true],
      ['Dörte@Sörensen.example.com', true],
      ['аджай@экзампл.рус', true],
      ['me@localhost', false],
      ['singleword', false],
      ['@@.com', false],
      [12345, false]
    ];
  }

  public function _jsonableProvider() : array {
    return [
      [[], true],
      [[1, 2, 3], true],
      [['a' => 1, 'b' =>2, 'c' => 3], true],
      [true, true],
      [false, true],
      [null, true],
      [new stdClass, true],
      [
        new class implements JsonSerializable {
          public function jsonSerialize() { return []; }
        },
        true
      ],

      [new ArrayObject, false],
      [fopen('php://memory', 'r'), false]
    ];
  }
}
