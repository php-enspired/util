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
  Closure,
  DateTime,
  DateTimeInterface,
  JsonSerializable,
  stdClass,
  Throwable,
  TypeError;

use at\util\ {
  tests\TestCase,
  Value,
  ValueException
};

/**
 * tests for Value methods.
 */
class ValueTest extends TestCase {

  /**
   * @covers Value::debug
   */
  public function testDebug() {
    $this->assertEquals("string(3) \"foo\"\n", Value::debug('foo'));
    $this->assertEquals("string(3) \"foo\"\nint(42)\n", Value::debug('foo', 42));

    $this->_expectException(new ValueException(ValueException::NO_EXPRESSIONS));
    Value::debug();
  }

  /**
   * @covers Value::is
   * @dataProvider _valueProvider
   *
   * @param mixed $value     subject value
   * @param array $expected  value type, example (pseudo)types value passes/fails
   */
  public function testIs($value, array $expected) {
    $this->assertTrue(Value::is($value, $expected['is']));
    $this->assertTrue(Value::is($value, $expected['type']));
    $this->assertTrue(Value::is($value, ...array_values($expected)));

    $this->assertFalse(Value::is($value, $expected['not']));
  }

  /**
   * @return array[]  testcases
   */
  public function _valueProvider() : array {
    return [
      [[], ['type' => Value::ARRAY, 'is' => Value::ARRAY, 'not' => Value::BOOL]],
      [true, ['type' => Value::BOOL, 'is' => Value::BOOL, 'not' => Value::FLOAT]],
      [false, ['type' => Value::BOOL, 'is' => Value::BOOL, 'not' => Value::INT]],
      ['strpos', ['type' => Value::STRING, 'is' => Value::CALLABLE, 'not' => Value::NULL]],
      [function () {}, ['type' => Closure::class, 'is' => Value::CALLABLE, 'not' => Value::RESOURCE]],
      [
        [DateTime::class, 'createFromFormat'],
        ['type' => Value::ARRAY, 'is' => Value::CALLABLE, 'not' => Value::STRING]
      ],
      [[], ['type' => Value::ARRAY, 'is' => Value::COUNTABLE, 'not' => Value::BOOL]],
      [
        new ArrayObject,
        ['type' => ArrayObject::class, 'is' => Value::COUNTABLE, 'not' => Value::FLOAT]
      ],
      [
        new DateTime,
        ['type' => DateTime::class, 'is' => Value::DATETIMEABLE, 'not' => Value::INT]
      ],
      [
        '@123456789',
        ['type' => Value::STRING, 'is' => Value::DATETIMEABLE, 'not' => Value::NULL]
      ],
      [
        'today',
        ['type' => Value::STRING, 'is' => Value::DATETIMEABLE, 'not' => Value::OBJECT]
      ],
      [0.5, ['type' => Value::FLOAT, 'is' => Value::FLOAT, 'not' => Value::STRING]],
      [0, ['type' => Value::INT, 'is' => Value::INT, 'not' => Value::ARRAY]],
      [[], ['type' => Value::ARRAY, 'is' => Value::ITERABLE, 'not' => Value::BOOL]],
      [
        new ArrayObject,
        ['type' => ArrayObject::class, 'is' => Value::ITERABLE, 'not' => Value::FLOAT]
      ],
      ['', ['type' => Value::STRING, 'is' => Value::JSONABLE, 'not' => Value::INT]],
      [true, ['type' => Value::BOOL, 'is' => Value::JSONABLE, 'not' => Value::NULL]],
      [0.5, ['type' => Value::FLOAT, 'is' => Value::JSONABLE, 'not' => Value::OBJECT]],
      [0, ['type' => Value::INT, 'is' => Value::JSONABLE, 'not' => Value::RESOURCE]],
      [[], ['type' => Value::ARRAY, 'is' => Value::JSONABLE, 'not' => Value::STRING]],
      [
        new stdClass,
        ['type' => stdClass::class, 'is' => Value::JSONABLE, 'not' => Value::ARRAY]
      ],
      [null, ['type' => Value::NULL, 'is' => Value::JSONABLE, 'not' => Value::BOOL]],
      [null, ['type' => Value::NULL, 'is' => Value::NULL, 'not' => Value::FLOAT]],
      [new stdClass, ['type' => stdClass::class, 'is' => Value::OBJECT, 'not' => Value::INT]],
      [
        fopen('php://temp', 'r'),
        ['type' => Value::RESOURCE, 'is' => Value::RESOURCE, 'not' => Value::NULL]
      ],
      ['', ['type' => Value::STRING, 'is' => Value::SCALAR, 'not' => Value::OBJECT]],
      [true, ['type' => Value::BOOL, 'is' => Value::SCALAR, 'not' => Value::STRING]],
      [0, ['type' => Value::INT, 'is' => Value::SCALAR, 'not' => Value::ARRAY]],
      [0.5, ['type' => Value::FLOAT, 'is' => Value::SCALAR, 'not' => Value::BOOL]],
      ['', ['type' => Value::STRING, 'is' => Value::STRING, 'not' => Value::INT]]
    ];
  }

  /**
   * @covers Value::hint
   * @dataProvider _valueProvider
   *
   * @param mixed $value     subject value
   * @param array $expected  value type, example (pseudo)types value passes/fails
   */
  public function testHint($value, array $expected) {
    $this->assertNull(Value::hint('is', $value, $expected['is']));
    $this->assertNull(Value::hint('type', $value, $expected['type']));
    $this->assertNull(Value::hint('any', $value, ...array_values($expected)));

    $this->_expectException(TypeError::class);
    Value::hint('not', $value, $expected['not']);
  }

  /**
   * @covers Value::type
   * @dataProvider _valueProvider
   *
   * @param mixed $value     subject value
   * @param array $expected  value type, example (pseudo)types value passes/fails
   */
  public function testType($value, array $expected) {
    $this->assertEquals($expected['type'], Value::type($value));
  }
}
