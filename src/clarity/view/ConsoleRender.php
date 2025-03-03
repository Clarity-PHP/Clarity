<?php
declare(strict_types=1);

namespace framework\clarity\view;

use framework\clarity\view\interfaces\ViewRendererInterface;

class ConsoleRender implements ViewRendererInterface
{
    const COLOR_RED = "\033[41m";
    const COLOR_WHITE = "\033[97m";
    const COLOR_RESET = "\033[0m";

    /**
     * @param string|null $view
     * @param array $params
     * @return string
     */
    public function render(?string $view = null, array $params = []): string
    {
        if (isset($params['message'], $params['status']) === true) {
            return $this->renderError($params);
        }

        return "Unknown view";
    }

    /**
     * @param array $params
     * @return string
     */
    private function renderError(array $params): string
    {
        $message = $params['message'] ?? 'An error occurred';

        $status = $params['status'] ?? 500;

        $trace = $params['trace'] ?? '';

        $output = self::COLOR_RED . self::COLOR_WHITE . "Error: $message\n" . self::COLOR_RESET;

        $output .= self::COLOR_RED . self::COLOR_WHITE . "Status: $status\n" . self::COLOR_RESET;

        if (empty($trace) === false) {
            $output .= "\n" . self::COLOR_RED . self::COLOR_WHITE . "Stack Trace:\n" . self::COLOR_RESET;

            $output .= self::COLOR_WHITE . $trace . self::COLOR_RESET;
        }

        return $output;
    }
}