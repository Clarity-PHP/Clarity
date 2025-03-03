<?php

namespace framework\clarity\view\interfaces;

interface ViewRendererInterface
{
    /**
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render(string $view, array $params = []): string;

    /**
     * @param string|null $layout
     * @return void
     */
    public function setLayout(?string $layout): void;

    /**
     * @return string|null
     */
    public function getLayout() : ?string;
}