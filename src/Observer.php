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
namespace PTK\Observer;

use PTK\Exceptlion\Type\InvalidTypeException;
use PTK\Exceptlion\Value\OutOfBoundsException;

/**
 * Implementação do design patter observer.
 *
 * @author Everton
 */
class Observer
{

    /**
     *
     * @var array Lista de variáveis observáveis.
     */
    protected array $observable = [];

    /**
     *
     * @var array Armazena os valores atuais das observables.
     */
    protected array $values = [];

    public function __construct()
    {
        
    }

    /**
     * Registra uma variável e o callback a ser chamado quando da sua modificação.
     * 
     * @param string $name Nome da variável.
     * @param type $callback O callback. O callback sempre dever receber como 
     * parâmetro o valor anterior e o modificado da variável.
     * 
     * Pode ser:
     * 
     * - string: interpretada como uma função myfunction($oldvalue, $newvalue)
     * - array: Nesta caso, de acordo com o tipo do primeiro elemento, pode ser:
     *  - string: no primeiro elemento está o nome da classe e no segundo o nome 
     * do método. Neste caso, será usada execução estática com ::
     *  - object: no primeiro elemento está a instância da classe e no segundo o 
     * nome do método.
     *  - callable: uma função anônima (lambda) ou arrow function.
     * 
     * 
     * @return void
     */
    public function register(string $name, $callback): void
    {
        $this->observable[$name] = $callback;
        $this->values[$name] = null;
    }

    public function unregister(string $name): void
    {
        if ($this->hasObservable($name) === false) {
            throw new OutOfBoundsException($name, array_keys($this->observable));
        }

        unset($this->observable[$name]);
        unset($this->values[$name]);
    }

    public function __get(string $name)
    {
        if ($this->hasObservable($name) === false) {
            throw new OutOfBoundsException($name, array_keys($this->observable));
        }

        return $this->values[$name];
    }

    public function __set(string $name, $value)
    {
        if ($this->hasObservable($name) === false) {
            throw new OutOfBoundsException($name, array_keys($this->observable));
        }

        $oldvalue = $this->values[$name];
        $this->values[$name] = $value;
        $this->runCallbacksFor($name, $oldvalue, $value);
    }

    protected function runCallbacksFor(string $name, $oldvalue, $newvalue): void
    {
        $callback = $this->observable[$name];

        if (is_string($callback)) {
            $this->runStringCallback($callback, $oldvalue, $newvalue);
            return;
        }

        if (is_array($callback)) {
            $this->runArrayCallback($callback, $oldvalue, $newvalue);
            return;
        }

        if (is_callable($callback)) {
            $this->runCallableCallback($callback, $oldvalue, $newvalue);
            return;
        }

        throw new InvalidTypeException(gettype($callback), ['string', 'array', 'callable']);
    }

    protected function runCallableCallback(callable $callback, $oldvalue, $newvalue): void
    {
        $callback($oldvalue, $newvalue);
    }

    protected function runArrayCallback(array $callback, $oldvalue, $newvalue): void
    {
        if (is_string($callback[1]) === false) {
            throw new InvalidTypeException(gettype($callback), ['string']);
        }

        $method = $callback[1];

        if (is_string($callback[0])) {
            $class = $callback[0];

            $class::$method($oldvalue, $newvalue);
            return;
        }

        if (is_object($callback[0])) {
            $instance = $callback[0];

            $instance->$method($oldvalue, $newvalue);
            return;
        }

        //Exception: se o primeiro elemento não é nem um objeto, nem uma string.
        throw new InvalidTypeException(gettype($callback), ['string', 'object']);
    }

    protected function runStringCallback(string $callback, $oldvalue, $newvalue): void
    {
        $callback($oldvalue, $newvalue);
    }

    public function hasObservable(string $name): bool
    {
        return key_exists($name, $this->observable);
    }
}
