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
 * Class Dispatcher
 *
 * The Dispatcher is the central nervous system to CoreFrameworks Pub/Sub architecture
 *
 * @author  Shalom Sam <shalom.s@coreframework.in>
 * @package Core\Events
 */
class Dispatcher implements DispatcherInterface
{
    private $listeners = [];

    /**
     * Add/Register Event listeners that listen for specific events
     *
     * @param   string      $eventName  The Event name
     * @param   callable    $listener   The Listener object that executes on broadcast
     * @param   int         $priority   The Listener with lower value fires before those with
     *                                  higher values. Defaults to 0
     *
     * @throws  \InvalidArgumentException
     *
     * @return  void
     */
    protected function addListener($eventName, $listener, $priority = 0)
    {
        $this->listeners[$eventName][$priority][] = $listener;
    }

    /**
     * @inheritdoc
     */
    public function addSubscriber(SubscriberInterface $subscriber)
    {
        $subscriber->subscribe($this);
    }

    public function removeSubscriber(SubscriberInterface $subscriber)
    {
        $subscriber->unSubscribe($this);
    }

    /**
     * @inheritdoc
     */
    public function broadcast($eventName, EventInterface $event = null)
    {
        if ($event === null) {
            $event = new Event($eventName, ['caller' => get_called_class()]);
        }

        $listeners = $this->getListeners($eventName);
        if (!empty($listeners)) {
            $this->dispatch($listeners, $eventName, $event);
        }

        return $event;
    }

    /**
     * @inheritdoc
     */
    public function getListenerPriority($eventName, $listener)
    {
        if (empty($this->listeners[$eventName])) {
            return null;
        }

        $_priority = null;
        $_listeners = $this->listeners[$eventName];

        foreach ($_listeners as $priority => $listeners) {
            foreach ($listeners as $k => $_listener) {
                if ($listener === $_listener) {
                    $_priority = $priority;
                }
            }
        }

        return $_priority;
    }

    /**
     * @inheritdoc
     */
    public function getListeners($eventName = null)
    {
        if ($eventName === null) {
            foreach ($this->listeners as $eventName => $listeners) {
                ksort($this->listeners[$eventName]);
                $this->listeners[$eventName] = array_values($this->listeners[$eventName]);
            }
            return $this->listeners;
        }
        ksort($this->listeners[$eventName]);
        return array_values($this->listeners[$eventName]);
    }

    /**
     * @inheritdoc
     */
    public function hasListeners($eventName)
    {
        return isset($this->listeners[$eventName]) && !empty($this->listeners[$eventName]);
    }

    /**
     * @inheritdoc
     */
    public function on($eventName, $listener, $priority = 0)
    {
        $this->addListener($eventName, $listener, $priority);
    }

    /**
     * @inheritdoc
     */
    public function removeListener($eventName, $listener)
    {
        if (empty($this->listeners[$eventName])) {
            return null;
        }

        $_listeners = $this->listeners[$eventName];
        foreach ($_listeners as $priority => $listeners) {
            foreach ($listeners as $k => $_listener) {
                if ($listener === $_listener) {
                    unset($listeners[$k]);
                }
            }

            if (!empty($listeners)) {
                $this->listeners[$eventName][$priority] = $listeners;
            } else {
                unset($this->listeners[$eventName][$priority]);
            }
        }
    }

    /**
     * Execute the given listeners of an event
     *
     * @param array $listeners
     * @param $eventName
     * @param Event $event
     */
    protected function dispatch(array $listeners, $eventName, Event $event)
    {
        foreach ($listeners as $listener) {
            foreach ($listener as $k => $v) {
                if ($event->isStopped()) {
                    return;
                }
                if ($v instanceof ListenerInterface) {
                    $v($event, $eventName, $this);
                } else {
                    call_user_func($v, $event, $eventName, $this);
                }
            }
        }
    }
}