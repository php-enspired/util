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

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * base class for at/util test cases.
 */
abstract class TestCase extends PHPUnitTestCase {

  /**
   * convenience function for setting exception expectations.
   *
   * @param string|Throwable $exception  the exception to expect
   */
  protected function _expectException($exception) {
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
}
