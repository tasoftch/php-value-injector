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

/**
 * ValueInjectorTest.php
 * TASoft Core
 *
 * Created on 17.06.18 08:50 by thomas
 */

use TASoft\Util\ValueInjector;
use PHPUnit\Framework\TestCase;

class ValueInjectorTest extends TestCase
{
    public function testValueInjection() {
        $obj = new TestClass();

        $vi = new ValueInjector($obj);

        $vi->special = 45;
        $vi->value = "Thomas";
        $obj->name = "Hallo";

        $this->assertEquals(["Hallo", "Thomas", 45], $obj->getValues());

        $ex = NULL;
        try {
            $this->assertEquals(45, $obj->special);
        } catch (\Throwable $e) {
            $ex = $e;
        }

        $this->assertNotNull($ex);

        $this->assertEquals(45, $vi->special);
    }

    public function testMethodForwarding() {
        $obj = new TestClass();

        $vi = new ValueInjector($obj);

        $this->assertEquals('OK', $vi->testProtected());
        $this->assertEquals([1,2,3,"Thomas"], $vi->testArguments(1,2,3,"Thomas"));
    }
}


class TestClass {
    public $name;
    protected $value;
    private $special;

    public function testPublic() {
        return "OK";
    }

    protected function testProtected() {
        return "OK";
    }

    private function testPrivate() {
        return "OK";
    }

    private function testArguments() {
        return func_get_args();
    }

    public function getValues() {
        return [
            $this->name,
            $this->value,
            $this->special
        ];
    }
}
