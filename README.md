Dispatcher Component
=========================

The Dispatcher component provides means by which your applications components can communicate with each other by using the Publish/Subscribe model.

## Usage
#### 1. Basic usage:
First create a Dispatcher Instance:
```php
$dispatcher = new Dispatcher();
```

Then subscribe to events using the following syntax:
```php
$dispatcher->on('eventFoo', function() { echo 'foo';});
```

The second parameter for the `on()` method can be `callable` like a **Closure** (*anonymous functions*) or can be instances of `Core\Events\ListenerInterface`.

An Example of listener Class can be as follows:
```php
class TestEventListener implements ListenerInterface
{
    /**
     * @var EventInterface
     */
    public $calledEvent;
    /**
     * @var string
     */
    public $eventName;
    /**
     * @var DispatcherInterface
     */
    public $dispatcher;

    public function __invoke(EventInterface $event, string $eventName = null, DispatcherInterface $dispatcher = null)
    {
        $this->calledEvent = $event;
        $this->eventName = $eventName;
        $this->dispatcher = $dispatcher;
    }
}
```
The `Listener` Class you define must implement the [`ListenerInterface`](src/Core/Events/ListenerInterface.php) which mandates the definition of the `invoke()` method.

#### 2. Event Subscribers:
The `Dispatcher` class provides for means to add Subscribers that can add their own set of event listeners.
You can add Subscribers using the `addSubscriber()` method as follows:
```php
$subscriber = new Subscriber();
$dispatcher->addSubscriber($subscriber);
```
The defined `Subscriber` class must implement the [`SubscriberInterface`](src/Core/Events/SubscriberInterface.php). 