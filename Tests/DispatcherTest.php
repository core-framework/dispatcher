<?php
/**
 * This file is part of the CoreFramework package.
 *
 * (c) Shalom Sam <shalom.s@coreframework.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Events\Tests;

use Core\Events\Dispatcher;
use Core\Events\DispatcherInterface;
use Core\Events\Event;
use Core\Events\EventInterface;
use Core\Events\ListenerInterface;
use Core\Events\SubscriberInterface;
use PHPUnit\Framework\TestCase;

class DispatcherTest extends TestCase
{
    const eventFoo = 'foo';
    const eventBar = 'bar';
    const eventFooBar = 'foobar';

    /**
     * @var Dispatcher
     */
    private $dispatcher;
    private $listener;

    protected function setUp()
    {
        $this->dispatcher = new Dispatcher();
        $this->listener = new TestEventListener();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testInitial()
    {
        $this->assertEquals([], $this->dispatcher->getListeners());
        $this->assertFalse($this->dispatcher->hasListeners(self::eventFoo));
        $this->assertFalse($this->dispatcher->hasListeners(self::eventBar));
        $this->assertFalse($this->dispatcher->hasListeners(self::eventFooBar));
    }

    public function testOnMethod()
    {
        $this->dispatcher->on(self::eventFoo, $this->listener);
        $this->dispatcher->on(self::eventBar, $this->listener);
        $this->assertTrue($this->dispatcher->hasListeners(self::eventFoo));
        $this->assertTrue($this->dispatcher->hasListeners(self::eventBar));
        $this->assertCount(1, $this->dispatcher->getListeners(self::eventFoo));
        $this->assertCount(1, $this->dispatcher->getListeners(self::eventBar));
        $this->assertCount(2, $this->dispatcher->getListeners());
    }

    public function testGetListenersPriority()
    {
        $listener1 = new TestEventListener(['name' => 'listener1']);
        $listener2 = new TestEventListener(['name' => 'listener2']);
        $listener3 = new TestEventListener(['name' => 'listener3']);
        $listener4 = new TestEventListener(['name' => 'listener4']);

        $this->dispatcher->on(self::eventFooBar, $listener1, 10);
        $this->dispatcher->on(self::eventFooBar, $listener2, 2);
        $this->dispatcher->on(self::eventFooBar, $listener3, 5);
        $this->dispatcher->on(self::eventFooBar, $listener4);

        $this->assertSame([
            array($listener4),
            array($listener2),
            array($listener3),
            array($listener1)
        ], $this->dispatcher->getListeners(self::eventFooBar));
    }

    public function testGetAllListenersPriority()
    {
        $listener1 = new TestEventListener(['name' => 'listener1']);
        $listener2 = new TestEventListener(['name' => 'listener2']);
        $listener3 = new TestEventListener(['name' => 'listener3']);
        $listener4 = new TestEventListener(['name' => 'listener4']);
        $listener5 = new TestEventListener(['name' => 'listener5']);
        $listener6 = new TestEventListener(['name' => 'listener6']);

        $this->dispatcher->on(self::eventFoo, $listener1, 10);
        $this->dispatcher->on(self::eventFoo, $listener2, 4);
        $this->dispatcher->on(self::eventFoo, $listener3, 6);

        $this->dispatcher->on(self::eventBar, $listener4, 8);
        $this->dispatcher->on(self::eventBar, $listener5, 3);
        $this->dispatcher->on(self::eventBar, $listener6, 7);

        $this->assertSame([
            'foo' => array([$listener2], [$listener3], [$listener1]),
            'bar' => array([$listener5], [$listener6], [$listener4])
        ], $this->dispatcher->getListeners());
    }

    public function testGetListenerPriority()
    {
        $listener1 = new TestEventListener(['name' => 'listener1']);
        $listener2 = new TestEventListener(['name' => 'listener2']);

        $this->dispatcher->on(self::eventFoo, $listener1, 5);
        $this->dispatcher->on(self::eventFoo, $listener2);

        $this->assertSame(5, $this->dispatcher->getListenerPriority(self::eventFoo, $listener1));
        $this->assertSame(0, $this->dispatcher->getListenerPriority(self::eventFoo, $listener2));

        $this->assertNull($this->dispatcher->getListenerPriority(self::eventBar, $listener1));
        $this->assertNull($this->dispatcher->getListenerPriority(self::eventFooBar, function() {}));
    }

    public function testBroadcast()
    {
        $called = 0;
        $listener2 = new TestEventListener(['name' => 'listener2']);
        $this->dispatcher->on(self::eventFoo, function () use (&$called) {
            ++$called;
        });
        $this->dispatcher->on(self::eventBar, $listener2);
        $this->dispatcher->on(self::eventFooBar, function (EventInterface $event) {
            $event->addData('name', 'foobar');
        });

        $this->dispatcher->broadcast(self::eventFoo);
        $this->assertEquals(1, $called);

        $this->dispatcher->broadcast(self::eventBar);
        $this->assertInstanceOf(Event::class, $listener2->calledEvent);
        $this->assertSame(self::eventBar, $listener2->calledEvent->getName());

        $fooBarEvent = $this->dispatcher->broadcast(self::eventFooBar);
        $this->assertInstanceOf(Event::class, $fooBarEvent);
        $this->assertSame(self::eventFooBar, $fooBarEvent->getData('name'));
    }

    public function testEventStopPropagation()
    {
        $called = 0;
        $listener1Called = $listener2Called = false;
        $listener1 = function (EventInterface $event) use (&$called, &$listener1Called) {
            ++$called;
            $listener1Called = true;
            $event->stop();
        };
        $listener2 = function () use (&$listener2Called) {
            $listener2Called = true;
        };

        $this->dispatcher->on(self::eventFoo, $listener1);
        $this->dispatcher->on(self::eventFoo, $listener1);
        $this->dispatcher->on(self::eventFoo, $listener2);

        $this->dispatcher->broadcast(self::eventFoo);

        $this->assertEquals(1, $called);
        $this->assertTrue($listener1Called);
        $this->assertFalse($listener2Called);
    }

    public function testBroadcastPriority()
    {
        $called = [];
        $listener1 = function () use (&$called) {
            $called[] = 1;
        };
        $listener2 = function () use (&$called) {
            $called[] = 2;
        };
        $listener3 = function () use (&$called) {
            $called[] = 3;
        };

        $this->dispatcher->on(self::eventFoo, $listener1, 2);
        $this->dispatcher->on(self::eventFoo, $listener2, 10);
        $this->dispatcher->on(self::eventFoo, $listener3, 20);

        $this->dispatcher->broadcast(self::eventFoo);

        $this->assertEquals([1,2,3], $called);
    }

    public function testRemoveListener()
    {
        $this->dispatcher->on(self::eventFoo, $this->listener);
        $this->assertTrue($this->dispatcher->hasListeners(self::eventFoo));
        $this->dispatcher->removeListener(self::eventFoo, $this->listener);
        $this->assertFalse($this->dispatcher->hasListeners(self::eventFoo));
    }

    public function testRemoveListenerWithClosure()
    {
        $listener = function () {};
        $this->dispatcher->on(self::eventFoo, $listener);
        $this->assertTrue($this->dispatcher->hasListeners(self::eventFoo));
        $this->dispatcher->removeListener(self::eventFoo, $listener);
        $this->assertFalse($this->dispatcher->hasListeners(self::eventFoo));
    }

    public function testAddRemoveSubscriber()
    {
        $subscriber = new TestSubscriber();
        $this->dispatcher->addSubscriber($subscriber);
        $this->assertTrue($this->dispatcher->hasListeners(self::eventFooBar));

        $this->dispatcher->removeSubscriber($subscriber);
        $this->assertFalse($this->dispatcher->hasListeners(self::eventFooBar));
    }

    public function testListenerArguments()
    {
        $listener = new TestEventListener();
        $this->dispatcher->on(self::eventFoo, $listener);
        $this->dispatcher->broadcast(self::eventFoo);

        $this->assertInstanceOf(EventInterface::class, $listener->calledEvent);
        $this->assertInstanceOf(DispatcherInterface::class, $listener->dispatcher);
        $this->assertSame(self::eventFoo, $listener->eventName);
        $this->assertSame($this->dispatcher, $listener->dispatcher);
    }
}

class TestEventListener implements ListenerInterface
{
    /**
     * @var EventInterface
     */
    public $calledEvent;
    public $eventName;
    public $dispatcher;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function __invoke(EventInterface $event, string $eventName = null, DispatcherInterface $dispatcher = null)
    {
        $this->calledEvent = $event;
        $this->eventName = $eventName;
        $this->dispatcher = $dispatcher;
    }
}

class TestSubscriber implements SubscriberInterface
{
    public $testSubscriberCalled = false;

    public $listener;

    public function __construct()
    {
        $this->listener = new TestEventListener(['name' => 'TestSubscriberListener']);
    }

    public function subscribe(DispatcherInterface $dispatcher)
    {
        $dispatcher->on('foobar', $this->listener, 0);
    }

    public function unSubscribe(DispatcherInterface $dispatcher)
    {
        $dispatcher->removeListener('foobar', $this->listener);
    }
}
