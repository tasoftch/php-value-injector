# Value Injector
The value injector object is a proxy that allows you to get, set or call non-accessible attributes of another object.

#### Install
```bin
$ composer require tasoft/value-injector
```

##### How it works
````php
<?php
use TASoft\Util\ValueInjector;

class PrivateClass {
    private $value;
    public function getValue() {
        return $this->value;
    }
}

$myObject = new PrivateClass();
echo $myObject->value; // Will fail
echo $myObject->getValue(); // Works

// But if you want to set the value, use my value injector:
$vi = new ValueInjector($myObject);
$vi->value = 23;

echo $myObject->getValue(); // 23
````
