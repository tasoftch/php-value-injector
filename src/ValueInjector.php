<?php
/**
 * Copyright (c) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace TASoft\Util;

/**
 * The value injector allows your to inject direct property values to an object ignoring the visibility state of the properties.
 * It can get and set properties, also calling methods
 * @package TASoft\Util
 */
class ValueInjector
{
    /** @var object  */
    private $object;

    /** @var string */
    private $objectContext;

    /** @var \Closure */
    private $_getter;
    /** @var \Closure */
    private $_setter;
    /** @var \Closure */
    private $_caller;

    /**
     * Pass the object you want to inject values.
     * @param object $object
     */
    public function __construct($object = NULL)
    {
        if(is_object($object))
            $this->setObject($object);
    }

    /**
     * Get the context from which the value injector sets and gets properties.
     * @return string
     */
    public function getObjectContext(): string
    {
        return $this->objectContext;
    }

    /**
     * Get the object.
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set the object to inject values. You may also pass a context for the callers.
     * If you pass null, the class name of the object will be the context, that means,
     * the callers are able to call as in the private context.
     * @param object $object
     * @param string|NULL $context
     */
    public function setObject($object, string $context = NULL): void
    {
        if(!is_object($object))
            throw new \InvalidArgumentException("Argument 1 passed to setValue must be an object");

        $this->object = $object;
        $this->objectContext = $context ?? get_class($object);
        $this->_caller = $this->_getter = $this->_setter = NULL;
    }

    /**
     * Creates the getter closure
     * @return \Closure
     * @internal
     */
    private function getGetter() {
        if(!$this->_getter) {
            $this->_getter = (function($name) {
                return $this->$name;
            })->bindTo($this->getObject(), $this->getObjectContext());
        }
        return $this->_getter;
    }

    /**
     * Creates the setter closure
     * @return \Closure
     * @internal
     */
    private function getSetter() {
        if(!$this->_setter) {
            $this->_setter = (function($name, $value) {
                $this->$name = $value;
            })->bindTo($this->getObject(), $this->getObjectContext());
        }
        return $this->_setter;
    }

    /**
     * Creates the caller closure
     * @return \Closure
     * @internal
     */
    private function getCaller() {
        if(!$this->_caller) {
            $this->_caller = (function($name, $arguments) {
                return call_user_func_array([$this, $name], $arguments);
            })->bindTo($this->getObject(), $this->getObjectContext());
        }
        return $this->_caller;
    }

    /**
     * Inject value to the object
     * @param $name
     * @param $value
     * @return $this
     */
    public function setValue($name, $value) {
        $setter = $this->getSetter();
        $setter($name, $value);
        return $this;
    }

    /**
     * Retrieve value from object
     * @param $name
     * @return mixed
     */
    public function getValue($name) {
        $getter = $this->getGetter();
        return $getter($name);
    }

    /**
     * Runs a closure in the object's context and using the object as $this variable.
     *
     * @param \Closure $closure
     * @param mixed ...$args
     * @return mixed
     */
    public function run(\Closure $closure, ...$args) {
        $closure = $closure->bindTo($this->getObject(), $this->getObjectContext());
        return call_user_func_array($closure, $args);
    }

    /**
     * Binds a closure to the object's context and using the object as $this variable.
     *
     * @param \Closure $closure
     * @return bool
     */
    public function bind(\Closure &$closure): bool {
        try {
            $closure = $closure->bindTo($this->getObject(), $this->getObjectContext());
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getValue($name);
    }

    /**
     * @param $name
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setValue($name, $value);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $caller = $this->getCaller();
        return $caller($name, $arguments);
    }
}