<?php

namespace framework\clarity\Http;

use framework\clarity\Http\interfaces\FormRequestFactoryInterface;
use framework\clarity\Http\interfaces\FormRequestInterface;
use InvalidArgumentException;

class FormRequestFactory implements FormRequestFactoryInterface
{

    /**
     * Создаёт экземпляр формы на основе класса.
     * Дополнительные зависимости могут быть переданы в конструктор.
     *
     * @param string $formClassName Имя класса формы.
     * @param array $params Параметры для конструктора формы.
     * @return FormRequestInterface
     */
    public function create(string $formClassName, array $params = []): FormRequestInterface
    {
        if (class_exists($formClassName) === false) {
            throw new InvalidArgumentException("Класс формы {$formClassName} не найден.");
        }

        return new $formClassName(...$params);
    }
}