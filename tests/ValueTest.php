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

use at\util\ {
  DateTime,
  Value,
  ValueException
};

use at\util\tests\TestCase;

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
   * @covers Value::filter
   * @dataProvider _filterProvider
   */
  public function testFilter(array $args, $expected) {
    [$value, $filter, $options] = $args;
    if ($expected instanceof Throwable) {
      $this->_expectException($expected);
    }

    // basic testcase
    $this->assertEquals($expected, Value::filter($value, $filter, $options));

    // throw on failure
    if ($expected === null) {
      $this->_expectException(
        new ValueException(
          ValueException::FILTER_FAILURE,
          ['filter' => $filter, 'value' => $value]
        )
      );
    }
    $options[Value::OPT_THROW] = true;
    $this->assertEquals($expected, Value::filter($value, $filter, $options));

    // default on failure
    $options[Value::OPT_DEFAULT] = 'foo';
    $this->assertEquals($expected ?? 'foo', Value::filter($value, $filter, $options));
  }

  /**
   * @covers Value::filterEach
   * @dataProvider _filterEachProvider
   */
  public function testFilterEach(array $args, $expected) {
    if ($expected instanceof Throwable) {
      $this->_expectException($expected);
    }

    $this->assertEquals($expected, Value::filterEach(...$args));
  }

  /**
   * @covers Value::filterMap
   * @dataProvider _filterMapProvider
   */
  public function testFilterMap(array $args, $expected) {
    if ($expected instanceof Throwable) {
      $this->_expectException($expected);
    }

    $this->assertEquals($expected, Value::filterMap(...$args));
  }

  /**
   * @covers Value::is
   * @dataProvider _isProvider
   */
  public function testIs(array $args, $expected) {
    $this->assertEquals($expected, Value::is(...$args));
  }

  /**
   * @covers Value::hint
   * @dataProvider _isProvider
   */
  public function testHint(array $args, $expected) {
    if (! $expected) {
      $this->_expectException($expected);
    }

    $this->assertIsNull(Value::hint(...$args));
  }

  /**
   * @covers Value::type
   * @dataProvider _typeProvider
   */
  public function testType(array $args, $expected) {
    $this->assertEquals($expected, Value::type(...$args));
  }


  /** @type array[]  examples of arrays for testcases. */
  protected $_exampleArrays = [
    'list' => [1, 2, 3],
    'list of lists' => [[1, 2, 3], [4, 5, 6], [7, 8, 9]],
    'list of maps' => [
      ['a' => 'A', 'b' => 'B', 'c' => 'C'],
      ['d' => 'D', 'e' => 'E', 'f' => 'F'],
      ['g' => 'G', 'h' => 'H', 'i' => 'I']
    ],
    'map' => ['a' => 'A', 'b' => 'B', 'c' => 'B'],
    'map of lists' => ['a' => [1, 2, 3], 'b' => [4, 5, 6], 'c' => [7, 8, 9]],
    'map of maps' => [
      'x' => ['a' => 'A', 'b' => 'B', 'c' => 'C'],
      'y' => ['d' => 'D', 'e' => 'E', 'f' => 'F'],
      'z' => ['g' => 'G', 'h' => 'H', 'i' => 'I']
    ]
  ];


  /**
   * @todo add bad filter case
   *
   * @return array[] {
   *    @type mixed $0  {
   *      @type mixed $0  filter() $val argument
   *      @type mixed $1  filter() $filter argument
   *      @type array $2  filter() $options argument
   *    }
   *    @type mixed $1  the expected result (filtered value or null)
   *  }
   */
  public function _filterProvider() : array {
    return array_merge(
      $this->_arrayFilterProvider(),
      $this->_boolFilterProvider(),
      $this->_callableFilterProvider(),
      $this->_dateTimeFilterProvider(),
      $this->_floatFilterProvider(),
      $this->_integerFilterProvider(),
      $this->_iterableFilterProvider(),
      $this->_jsonableFilterProvider(),
      $this->_nullFilterProvider(),
      $this->_objectFilterProvider(),
      $this->_resourceFilterProvider(),
      $this->_stringableFilterProvider()
    );
  }

  /**
   * @return array[] {
   *    @type mixed $0  {
   *      @type mixed $0  filter() $val argument
   *      @type mixed $1  filter() $filter argument
   *      @type array $2  filter() $options argument
   *    }
   *    @type mixed $1  the expected result (filtered value or null)
   *  }
   */
  public function _arrayFilterProvider() : array {
    $testcases = [
      [[true, Value::ARRAY, []], null],
      [[function () {}, Value::ARRAY, []], null],
      [[1.2, Value::ARRAY, []], null],
      [[1, Value::ARRAY, []], null],
      [[new stdClass, Value::ARRAY, []], null],
      [[fopen('php://memory', 'r'), Value::ARRAY, []], null],
      [['a', Value::ARRAY, []], null]
    ];

    foreach ($this->_exampleArrays as $type => $array) {
      $testcases[] = [[$array, Value::ARRAY, []], $array];
    }

    return $testcases;
  }

  /**
   * @return array[] {
   *    @type mixed $0  {
   *      @type mixed $0  filter() $val argument
   *      @type mixed $1  filter() $filter argument
   *      @type array $2  filter() $options argument
   *    }
   *    @type mixed $1  the expected result (filtered value or null)
   *  }
   */
  public function _boolFilterProvider() : array {
    return [
      [[true, Value::BOOL, []], true],
      [[false, Value::BOOL, []], false],
      [[1, Value::BOOL, []], true],
      [[0, Value::BOOL, []], false],
      [['true', Value::BOOL, []], true],
      [['false', Value::BOOL, []], false],
      [['on', Value::BOOL, []], true],
      [['off', Value::BOOL, []], false],
      [['yes', Value::BOOL, []], true],
      [['no', Value::BOOL, []], false],

      [[[], Value::BOOL, []], null],
      [[function () {}, Value::BOOL, []], null],
      [[1.2, Value::BOOL, []], null],
      [[10, Value::BOOL, []], null],
      [[new stdClass, Value::BOOL, []], null],
      [[fopen('php://memory', 'r'), Value::BOOL, []], null],
      [['a', Value::BOOL, []], null]
    ];
  }

  /**
   * @return array[] {
   *    @type mixed $0  {
   *      @type mixed $0  filter() $val argument
   *      @type mixed $1  filter() $filter argument
   *      @type array $2  filter() $options argument
   *    }
   *    @type mixed $1  the expected result (filtered value or null)
   *  }
   */
  public function _callableFilterProvider() : array {
    $callables = [
      'strpos',
      function () {},
      [DateTime::class, 'createFromFormat'],
      [new DateTime, 'format'],
      new class () { public function __invoke() {} }
    ];

    $testcases = [
      [[[], Value::CALLABLE, []], null],
      [[true, Value::CALLABLE, []], null],
      [[1.2, Value::CALLABLE, []], null],
      [[1, Value::CALLABLE, []], null],
      [[new stdClass, Value::CALLABLE, []], null],
      [[fopen('php://memory', 'r'), Value::CALLABLE, []], null],
      [['a', Value::CALLABLE, []], null]
    ];

    foreach ($callables as $callable) {
      $testcases[] = [[$callable, Value::CALLABLE, []], $callable];
    }

    return $testcases;
  }

  /**
   * @return array[] {
   *    @type mixed $0  {
   *      @type mixed $0  filter() $val argument
   *      @type mixed $1  filter() $filter argument
   *      @type array $2  filter() $options argument
   *    }
   *    @type mixed $1  the expected result (filtered value or null)
   *  }
   */
  public function _dateTimeFilterProvider() : array {
    $datetimeables = [
      'today',
      -14186516,
      292660329.009,
      new DateTime
    ];

    $testcases = [
      [[[], Value::DATETIME, []], null],
      [[true, Value::DATETIME, []], null],
      [[function () {}, Value::DATETIME, []], null],
      [[new stdClass, Value::DATETIME, []], null],
      [[fopen('php://memory', 'r'), Value::DATETIME, []], null],
      [['a', Value::DATETIME, []], null]
    ];

    foreach ($datetimeables as $datetimeable) {
      $testcases[] = [[$datetimeable, Value::DATETIME, []], $datetimeable];
    }

    return $testcases;
  }

  /**
   * @return array[] {
   *    @type mixed $0  {
   *      @type mixed $0  filter() $val argument
   *      @type mixed $1  filter() $filter argument
   *      @type array $2  filter() $options argument
   *    }
   *    @type mixed $1  the expected result (filtered value or null)
   *  }
   */
  public function _floatFilterProvider() : array {
    return [
      [[1.2, Value::FLOAT, []], 1.2],
      [[-1.2, Value::FLOAT, []], -1.2],
      [[1, Value::FLOAT, []], 1.0],
      [[-1, Value::FLOAT, []], -1.0],
      [['42', Value::FLOAT, []], 42.0],
      [['73.21', Value::FLOAT, []], 73.21],
      [['-21.73', Value::FLOAT, []], -21.73],

      [[[], Value::FLOAT, []], null],
      [[true, Value::FLOAT, []], null],
      [[function () {}, Value::FLOAT, []], null],
      [[new stdClass, Value::FLOAT, []], null],
      [[fopen('php://memory', 'r'), Value::FLOAT, []], null],
      [['a', Value::FLOAT, []], null]
    ];
  }

  /**
   * @return array[] {
   *    @type mixed $0  {
   *      @type mixed $0  filter() $val argument
   *      @type mixed $1  filter() $filter argument
   *      @type array $2  filter() $options argument
   *    }
   *    @type mixed $1  the expected result (filtered value or null)
   *  }
   */
  public function _integerFilterProvider() : array {
    return [
      [[1, Value::INT, []], 1],
      [[-1, Value::INT, []], -1],
      [['42', Value::INT, []], 42],
      [['-73', Value::INT, []], -73],

      [[[], Value::INT, []], null],
      [[true, Value::INT, []], null],
      [[function () {}, Value::INT, []], null],
      [[1.2, Value::INT], null],
      [[new stdClass, Value::INT, []], null],
      [[fopen('php://memory', 'r'), Value::INT, []], null],
      [['a', Value::INT, []], null]
    ];
  }

  /**
   * @return array[] {
   *    @type mixed $0  {
   *      @type mixed $0  filter() $val argument
   *      @type mixed $1  filter() $filter argument
   *      @type array $2  filter() $options argument
   *    }
   *    @type mixed $1  the expected result (filtered value or null)
   *  }
   */
  public function _iterableFilterProvider() : array {
    $iterables = [
      [1, 2, 3],
      (object) [1, 2, 3],
      new ArrayObject([1, 2, 3]),
      function () { foreach ([1, 2, 3] as $n) { yield $n; } }
    ];

    $testcases = [
      [[true, Value::ITERABLE, []], null],
      [[function () {}, Value::ITERABLE, []], null],
      [[1.2, Value::ITERABLE, []], null],
      [[1, Value::ITERABLE, []], null],
      [[fopen('php://memory', 'r'), Value::ITERABLE, []], null],
      [['a', Value::ITERABLE, []], null]
    ];

    foreach ($iterables as $iterable) {
      $testcases[] = [[$iterable, Value::ITERABLE, []], $iterable];
    }

    return $testcases;
  }

  /**
   * @return array[] {
   *    @type mixed $0  {
   *      @type mixed $0  filter() $val argument
   *      @type mixed $1  filter() $filter argument
   *      @type array $2  filter() $options argument
   *    }
   *    @type mixed $1  the expected result (filtered value or null)
   *  }
   */
  public function _jsonableFilterProvider() : array {
    return [
      [[[], Value::JSONABLE, []], '[]'],
      [[['a' => 'A'], Value::JSONABLE, []], '{"a": "A"}'],
      [[(object) ['a' => 'A'], Value::JSONABLE, []], '{"a": "A"}'],
      [
        [
          new class implements JsonSerializable {
            public function jsonSerialize() { return ['a' => 'A']; }
          },
          Value::JSONABLE,
          []
        ],
        '{"a": "A"}'
      ],
      [[true, Value::JSONABLE, []], 'true'],
      [[function () {}, Value::JSONABLE, []], null],
      [[1.2, Value::JSONABLE, []], '1.2'],
      [[1, Value::JSONABLE, []], '1'],
      [[fopen('php://memory', 'r'), Value::JSONABLE, []], null],
      [['a', Value::JSONABLE, []], 'a']
    ];
  }

  /**
   * @return array[] {
   *    @type mixed $0  {
   *      @type mixed $0  filter() $val argument
   *      @type mixed $1  filter() $filter argument
   *      @type array $2  filter() $options argument
   *    }
   *    @type mixed $1  the expected result (filtered value or null)
   *  }
   */
  public function _nullFilterProvider() : array {
    return [
      [[true, Value::NULL, []], null],
      [[function () {}, Value::NULL, []], null],
      [[1.2, Value::NULL, []], null],
      [[1, Value::NULL, []], null],
      [[null, Value::NULL, []], null],  // ironic, isn't it
      [[new stdClass, Value::NULL, []], null],
      [[fopen('php://memory', 'r'), Value::NULL, []], null],
      [['a', Value::NULL, []], null]
    ];
  }

  /**
   * @return array[] {
   *    @type mixed $0  {
   *      @type mixed $0  filter() $val argument
   *      @type mixed $1  filter() $filter argument
   *      @type array $2  filter() $options argument
   *    }
   *    @type mixed $1  the expected result (filtered value or null)
   *  }
   */
  public function _objectFilterProvider() : array {
    $closure = function () {};
    $object = new stdClass;

    return [
      [[true, Value::OBJECT, []], null],
      [[$closure, Value::OBJECT, []], $closure],
      [[1.2, Value::OBJECT, []], null],
      [[1, Value::OBJECT, []], null],
      [[null, Value::OBJECT, []], null],
      [[$object, Value::OBJECT, []], $object],
      [[fopen('php://memory', 'r'), Value::OBJECT, []], null],
      [['a', Value::OBJECT, []], null]
    ];
  }

  /**
   * @return array[] {
   *    @type mixed $0  {
   *      @type mixed $0  filter() $val argument
   *      @type mixed $1  filter() $filter argument
   *      @type array $2  filter() $options argument
   *    }
   *    @type mixed $1  the expected result (filtered value or null)
   *  }
   */
  public function _resourceFilterProvider() : array {
    $resource = fopen('php://memory', 'r');

    return [
      [[true, Value::RESOURCE, []], null],
      [[function () {}, Value::RESOURCE, []], null],
      [[1.2, Value::RESOURCE, []], null],
      [[1, Value::RESOURCE, []], null],
      [[null, Value::RESOURCE, []], null],
      [[new stdClass, Value::RESOURCE, []], null],
      [[$resource, Value::RESOURCE, []], $resource],
      [['a', Value::RESOURCE, []], null]
    ];
  }

  /**
   * @return array[] {
   *    @type mixed $0  {
   *      @type mixed $0  filter() $val argument
   *      @type mixed $1  filter() $filter argument
   *      @type array $2  filter() $options argument
   *    }
   *    @type mixed $1  the expected result (filtered value or null)
   *  }
   */
  public function _stringableFilterProvider() : array {
    return [
      [['a', Value::STRING, []], 'a'],
      [[1.2, Value::STRING, []], '1.2'],
      [[1, Value::STRING, []], '1'],
      [
        [
          new class { public function __toString() { return 'foo'; } },
          Value::STRING,
          []
        ],
        'foo'
      ],

      [[true, Value::STRING, []], null],
      [[function () {}, Value::STRING, []], null],
      [[null, Value::STRING, []], null],
      [[new stdClass, Value::STRING, []], null],
      [[fopen('php://memory', 'r'), Value::STRING, []], $resource]
    ];
  }
}
