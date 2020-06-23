<?php
/*
 * The MIT License
 *
 * Copyright 2020 Everton.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Testes para PTK\Observer\Observer
 *
 * @author Everton
 */
class ObserverTest extends PHPUnit\Framework\TestCase
{

    protected static ?\PTK\Observer\Observer $observer;

    public function __construct()
    {
        parent::__construct();
    }

    public static function setUpBeforeClass(): void
    {
        self::$observer = new \PTK\Observer\Observer();
        
        require_once 'tests/assets/testingCallbackFunction.php';
        require_once 'tests/assets/testingClassCallback.php';
        
        
    }

    public function testRegister()
    {
        self::$observer->register('test', 'testingCallbackFunction');

        $this->assertTrue(self::$observer->hasObservable('test'));
    }

    public function testHasObservable()
    {
        $this->assertTrue(self::$observer->hasObservable('test'));
        $this->assertFalse(self::$observer->hasObservable('nonexists'));
    }

    public function testUnregister()
    {
        self::$observer->unregister('test');
        $this->assertFalse(self::$observer->hasObservable('test'));
    }
    
    public function testMagicGetAndSet()
    {
        self::$observer->register('test1', 'testingCallbackFunction');
        self::$observer->test1 = 1234;
        $this->assertEquals(1234, self::$observer->test1);
    }
    
    public function testCallbackIsStringFunctionName()
    {
        self::$observer->register('test2', 'testingCallbackFunction');
        self::$observer->test2 = 'abcd';
        $this->assertEquals('abcd', self::$observer->test2);
    }
    
    public function testCallbackIsArrayWithStringClassName()
    {
        self::$observer->register('test3', ['testingClassCallback', 'testStaticCallback']);
        self::$observer->test3 = 'abcd';
        $this->assertEquals('abcd', self::$observer->test3);
    }
    
    public function testCallbackIsArrayWithInstanceClass()
    {
        self::$observer->register('test4', [new testingClassCallback(), 'testInstanceCallback']);
        self::$observer->test4 = 'abcd132131';
        $this->assertEquals('abcd132131', self::$observer->test4);
    }
    
    public function testMagicGetReturnNull()
    {
        self::$observer->register('test5', 'testingCallbackFunction');
        $this->assertNull(self::$observer->test5);
    }
}
