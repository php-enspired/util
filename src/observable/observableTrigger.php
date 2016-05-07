<?php
/**
 * @package    at.mixin
 * @version    0.4[20160424]
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GNU GPL V2 <http://gnu.org/licenses/gpl-2.0.txt>
 */
declare( strict_types = 1 );
namespace at\mixin;

use at\util\Regex;

/**
 * default _parseTrigger() implementation for observer and observable traits. */
trait observableTrigger {

  /**
   * parses a string as a trigger regex.
   *
   * @param string $trigger  the event regex or literal event name
   * @return string          the parsed event regex
   */
  protected function _parseTrigger( string $trigger ) : string {
    if ( Regex::is_valid( $trigger ) ) {
      return $trigger;
    }
    if ( Regex::is_valid( "(^{$trigger}\b)ui" ) ) {
      return "(^{$trigger}\b)ui";
    }
    return '(^' . preg_quote( $trigger ) . '\b)ui';
  }
}
