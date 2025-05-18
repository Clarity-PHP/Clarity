<?php

namespace framework\clarity\Http;

use framework\clarity\Http\interfaces\FormRequestInterface;

class FormRequest implements FormRequestInterface
{
    private array $rules = [];
    private array $errors = [];
    private array $values = [];
    private bool $skipEmptyValues = false;

    /**
     * @return array
     */
    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * @param array $attributes
     * @param array|string $rule
     * @return void
     */
    public function addRule(array $attributes, array|string $rule): void
    {
        if (is_string($rule) === true) {
            $rule = [$rule];
        }

        foreach ($attributes as $attribute) {
            $this->rules[$attribute] = array_merge($this->rules[$attribute] ?? [], $rule);
        }
    }

    /**
     * @return void
     */
    public function validate(): void
    {
        foreach ($this->rules as $attribute => $rule) {
            if (
                $rule === 'required'
                && empty($this->values[$attribute]) === true
            ) {
                $this->addError($attribute, 'This field is required');
            }
        }
    }

    /**
     * @param string $attribute
     * @param string $message
     * @return void
     */
    public function addError(string $attribute, string $message): void
    {
        $this->errors[$attribute] = $message;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return void
     */
    public function setSkipEmptyValues(): void
    {
        $this->skipEmptyValues = true;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        if ($this->skipEmptyValues === false) {
            return $this->values;
        }

        return array_filter(
            $this->values,
            fn($v) => !($v === '' || $v === null),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @param array $values
     * @return void
     */
    public function setValues(array $values): void
    {
        $this->values = $values;
    }
}