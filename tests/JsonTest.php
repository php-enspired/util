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

use stdClass;

use at\util\ {
  Json,
  JsonException,
  tests\TestCase
};

/**
 * Tests for json encoding/decoding utilities.
 */
class JsonTest extends TestCase {

  /**
   * @covers Json::decode
   * @dataProvider _decodeProvider
   *
   * @param mixed|null  $data  decoded data if json is valid; null otherwise
   * @param string      $json  subject json
   */
  public function testDecode($data, string $json) {
    if ($data === null && $json !== 'null') {
      $this->_expectException(JsonException::class);
    }

    $this->assertEquals($data, Json::decode($json));
  }

  /**
   * @return array[]  testcases
   */
  public function _decodeProvider() : array {
    return array_filter(
      $this->_jsonProvider(),
      function ($a) { return $a[1] !== null; }
    );
  }

  /**
   * @covers Json::encode
   * @dataProvider _encodeProvider
   *
   * @param mixed       $data  subject data
   * @param string|null $json  encoded json if data is jsonable; null otherwise
   */
  public function testEncode($data, ?string $json) {
    if ($json === null) {
      $this->_expectException(JsonException::class);
    }

    $this->assertEquals($json, Json::encode($data));
  }

  /**
   * @return array[]  testcases
   */
  public function _encodeProvider() : array {
    return array_filter(
      $this->_jsonProvider(),
      function ($a) { return $a[0] !== null && $a[1] !== 'null'; }
    );
  }

  /**
   * @return array[]  testcases
   */
  public function _jsonProvider() : array {
    return [
      ['', '""'],
      [[], '[]'],
      [new stdClass(), '{}'],
      ['foo', '"foo"'],
      [1, "1"],
      [0.5, "0.5"],
      [true, 'true'],
      [false, 'false'],
      [null, 'null'],

      [fopen('php://temp', 'r'), null],

      [null, '"'],
      [null, ']'],
      [null, '{'],
      [null, '"{]'],
      [null, "\t[]"],
      [null, "'foo'"]
    ];
  }

  /**
   * @covers Json::decode
   * @dataProvider _decodeProvider
   *
   * @param mixed       $data  subject data
   * @param string|null $json  encoded json if data is jsonable; null otherwise
   */
  public function testIsValid($data, ?string $json) {
    if ($data === null && $json !== 'null') {
      $this->assertFalse(Json::isValid($json, $error));
      $this->assertNotEmpty($error);
      return;
    }

    $this->assertTrue(Json::isValid($json, $error));
    $this->assertNull($error);
  }
}
