<?php
/**
 * @package    at.mixin
 * @version    0.4[20160424]
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GNU GPL V2 <http://gnu.org/licenses/gpl-2.0.txt>
 */
declare( strict_types = 1 );
namespace at\util\observable\api;

/**
 * refinement of SplObserver.
 * provides for named events, filtered subscriptions, and passing arbitrary data with updates.
 *
 * implementations MUST be completely interoperable with the native SplSubject/SplObserver interfaces.
 */
interface Observer extends \SplObserver {

  /**
   * removes an update handler or triggers.
   *
   * passing both a handler and trigger list will remove those triggers from that handler;
   * if the handler's trigger list is emptied it will be removed.
   *
   * passing only a handler will remove that handler.
   *
   * passing only trigger(s) will remove those triggers from all handlers.
   * as above, if handler's trigger list is emptied, it is removed.
   *
   * @param callable|null $handler     subject handler
   * @param string|string[]|null       event names to remove
   * @throws InvalidArgumentException  if both $handler and $triggers are null
   */
  public function off( callable $handler=null, $triggers=null );

  /**
   * registers an update handler for given events.
   *
   * handlers should use the following signature:
   *  handler( SplSubject $subject [, string $eventName [, mixed …$args]] ) : void
   *
   * event "triggers" are regular expressions or literal strings.
   * pass NULL register a handler for "all updates."
   *
   * @param callable             $handler   the handler to register
   * @param string|string[]|null $triggers  event names to invoke this handler for
   */
  public function on( callable $handler, $triggers );

  /**
   * @see <http://php.net/SPLObserver.update>
   * in addition:
   *
   * an event name may be passed as the second arg, and will trigger any matching handlers.
   * implementations SHOULD provide for default handlers for empty event names.
   *
   * additional, arbitrary args may be passed after the event name,
   * and will be passed along to invoked handlers.
   *
   * @param SplSubject $subject   the subject of the update
   * @param string     $1         event name
   * @param mixed      $…         additional arguments for update
   * @throws ObservableException  if an uncaught exception is thrown during update
   */
  public function update( \SplSubject $subject );
}
