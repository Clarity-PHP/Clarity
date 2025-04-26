<?php

namespace framework\clarity\Http\interfaces;

interface FormRequestFactoryInterface
{
    /**
     * @param string $formClassName
     * @return FormRequestInterface
     */
    public function create(string $formClassName): FormRequestInterface;
}