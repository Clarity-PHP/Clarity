<?php

namespace framework\clarity\Http\interfaces;

interface FormRequestInterface
{
    /**
     * @return array
     */
    public function rules(): array;

    /**
     * @param array $attributes
     * @param array|string $rule
     * @return void
     */
    public function addRule(array $attributes, array|string $rule): void;

    /**
     * @return void
     */
    public function validate(): void;

    /**
     * @param string $attribute
     * @param string $message
     * @return void
     */
    public function addError(string $attribute, string $message): void;

    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @return void
     */
    public function setSkipEmptyValues(): void;

    /**
     * @return array
     */
    public function getValues(): array;
}