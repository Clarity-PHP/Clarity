<?php

namespace framework\clarity\EventDispatcher\interfaces;

interface ObserverFactoryInterface
{
    /**
     * @param string $observerClass
     * @return ObserverInterface
     */
    public function create(string $observerClass): ObserverInterface;

    /**
     * @param string $class
     * @param ObserverInterface $param
     * @return mixed
     */
    public function register(string $class, ObserverInterface $param);
}