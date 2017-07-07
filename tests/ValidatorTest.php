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

use Throwable;
use at\util\Validator,
    at\util\ValidatorException;
use PHPUnit\Framework\TestCase;

/**
 * in progress.
 */
class ValidatorTest extends TestCase {

  /**
   * mock rules.
   *
   * @type callable $_pass  rule which always passes.
   * @type callable $_fail  rule which always fails.
   */
  protected $_pass;
  protected $_fail;

  /**
   * {@inheritDoc}
   * @see https://phpunit.de/manual/current/en/fixtures.html
   */
  public function setUpBeforeClass() {
    $this->_pass = function () { return true; };
    $this->_fail = function () { return false; };
  }

  public function testAll () {}
  public function testAny () {}
  public function testAtLeast () {}
  public function testAtMost () {}
  public function testIf () {}
  public function testNone () {}
  public function testOne () {}
  public function testUnless () {}

  public function testAfter () {}
  public function testAlways () {}
  public function testBefore () {}
  public function testBetween () {}
  public function testDuring () {}
  public function testEquals () {}
  public function testFrom () {}
  public function testGreater () {}
  public function testIn () {}
  public function testIsType () {}
  public function testLess () {}
  public function testMatches () {}
  public function testNever () {}
  public function testSize () {}

  protected function rulesetProvider() : array {
    $pass = $this->_pass;
    $fail = $this->_fail;
  }
}
