<?php
/**
 * This file is part of the CoreFramework package.
 *
 * (c) Shalom Sam <shalom.s@coreframework.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Events;

/**
 * Interface DispatcherInterface
 * @package Core\Events
 */
interface DispatcherInterface
{
    /**
     * Add a Subscriber that subscribes that provides access to subscribe to all events registered with
     * the Dispatcher.
     *
     * @param   SubscriberInterface     $subscriber     Grants the capability to the subscribed class
     *                                                  to add its own listeners.
     *
     * @return  void
     */
    public function addSubscriber(SubscriberInterface $subscriber);

    /**
     * Broadcast to all registered listeners.
     *
     * @param   string          $eventName  The Event name.
     * @param   EventInterface  $event      The Event object that contains all the relevant event data.
     *
     * @return  EventInterface  Returns the Event that triggered this function.
     */
    public function broadcast($eventName, EventInterface $event);

    /**
     * Gets registered listener(s) by event name.
     *
     * @param   string  $eventName  The Event name.
     *
     * @return  array   Returns the registered listener.
     */
    public function getListeners($eventName = null);

    /**
     * Check if event (name) has any registered listeners.
     *
     * @param   string  $eventName  The Event name.
     *
     * @return  bool    False if event has no listeners.
     */
    public function hasListeners($eventName);

    /**
     * Add/Register Event listeners that listen for specific events.
     *
     * @param   string      $eventName  The Event name.
     * @param   callable    $listener   The Listener object that executes on broadcast.
     * @param   int         $priority   The Listener with lower value fires before those with
     *                                  higher values. Defaults to 0.
     *
     * @throws  \InvalidArgumentException
     *
     * @return  void
     */
    public function on($eventName, $listener, $priority = 0);

    /**
     * Returns the priority of the given Event Listener.
     *
     * @param   string      $eventName  The Event Name.
     * @param   callable    $listener   The Listener to search by.
     *
     * @return null|int
     */
    public function getListenerPriority($eventName, $listener);

    /**
     * @param   string      $eventName  The Event Name.
     * @param   callable    $listener   The Listener to search by.
     *
     * @return null|void
     */
    public function removeListener($eventName, $listener);
}