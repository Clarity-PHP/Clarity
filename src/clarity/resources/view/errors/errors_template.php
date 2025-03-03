<?php

/** @var int $code */
/** @var string $message */
/** @var array $trace */
/** @var string $file */
/** @var int $line */
/** @var string $environment */

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?= $code ?></title>
</head>
<body>
<?php if ($environment === 'production'): ?>
    <div class="error-container">
        <p class="error-message">
            <?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>
        </p>
        <h1>
            Упс! Что-то пошло не так, но мы уже работаем над устранением проблемы!
        </h1>
        <p>
            Пожалуйста, не переживайте — наша команда уже в курсе и скоро всё исправит.
        </p>
        <div>
    </div>

<?php endif; ?>

<?php if ($environment !== 'production'): ?>
    <h1>
        Ошибка:
        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
    </h1>
<?php endif; ?>

<?php if ($environment !== 'production'): ?>
    <div class="error-trace">
    <pre>
        <?php

        $traceDetails = '';

        $firstError = true;

        foreach ($trace as $index => $entry) {
            $file = $entry['file'] ?? 'N/A';

            $line = $entry['line'] ?? 'N/A';

            $function = $entry['function'] ?? 'N/A';

            $args = $entry['args'] ?? [];

            $formattedArgs = array_map(function ($arg) {
                if (is_array($arg) === true || is_object($arg) === true) {
                    return json_encode($arg, JSON_PRETTY_PRINT) ?: '[Invalid JSON]';
                }
                return htmlspecialchars((string)$arg, ENT_QUOTES, 'UTF-8');
            }, $args);

            $traceLine = "<div class='trace-item'>";

            $traceLine .= "#{$index} File: {$file} | Line: {$line} | Function: {$function} | Args: " . implode(', ', $formattedArgs);

            $traceLine .= "</div>";


            if ($file !== 'N/A' && file_exists($file) === true) {
                $lines = file($file);

                $startLine = max(0, $line - 10);

                $endLine = min(count($lines), $line + 7);

                $codeSnippet = array_slice($lines, $startLine, $endLine - $startLine);

                foreach ($codeSnippet as $i => $codeLine) {
                    $lineNumber = $startLine + $i + 1;

                    $class = ($lineNumber == $line) ? 'highlighted-line' : 'code-line';

                    $codeSnippet[$i] = "<div class='{$class}'><span class='line-number'>{$lineNumber}</span> " . htmlspecialchars($codeLine) . "</div>";
                }

                $traceLine .= "<div class='code-snippet'>" . implode('', $codeSnippet) . "</div>";
            }


            if ($firstError === true) {
                $traceDetails .= "<div class='main-error'>{$traceLine}</div>";

                $firstError = false;

                continue;
            }

            $traceDetails .= "<details><summary>Error #{$index} $file</summary>{$traceLine}</details>";
        }

        echo $traceDetails;
        ?>
    </pre>
    </div>
<?php endif; ?>
</body>
</html>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        height: 100%;
        background-color: #181818;
        color: #e0e0e0;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: flex-start;
        padding: 20px;
        overflow-y: auto;
    }

    h1 {
        font-size: 32px;
        color: #27ae60;
        margin: 0;
        padding: 20px 0;
    }

    .error-trace {
        background-color: #2f2f2f;
        color: #e0e0e0;
        font-family: 'Courier New', Courier, monospace;
        padding: 10px;
        border-radius: 8px;
        width: 100%;
        line-height: 1.5;
        font-size: 14px;
        border: 1px solid #444;
        max-height: none;
        overflow-y: auto;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
        word-wrap: break-word;
    }

    /* Для строки с главной ошибкой */
    .error-trace .main-error {
        background-color: #3a3a3a;
        border-left: 3px solid #27ae60;
        padding: 10px;
        margin-bottom: 15px;
        color: #27ae60;
    }

    .error-trace .code-snippet {
        background-color: #2e2e2e;
        padding: 12px 16px;
        border: 1px solid #444;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4);
        font-size: 14px;
        color: #d4d4d4;
        white-space: pre-wrap;
        word-break: break-word;
        line-height: 1.6;
        margin-top: 16px;
        overflow: auto;
        max-height: 500px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .error-trace .code-snippet:hover {
        background-color: #3a3a3a;
        color: #ffffff;
    }

    /* Стили для спойлеров */
    .error-trace details {
        background-color: #3a3a3a;
        border: 1px solid #444;
        padding: 10px;
        border-radius: 5px;
        margin-top: 10px;
    }

    .error-trace details summary {
        font-weight: bold;
        color: #58a6ff;
        cursor: pointer;
    }

    .error-trace details[open] summary {
        color: #0066cc;
    }

    .stack-trace-header {
        font-size: 24px;
        color: #27ae60;
        margin-top: 0;
        padding-bottom: 10px;
    }

    .error-message {
        color: #ecf0f1;
    }

    .highlighted-line {
        position: relative;
        background-color: #ffcc00;
        color: #333;
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 5px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
        display: inline-block;
        overflow: hidden;
    }

    .line-number {
        position: relative;
        padding-left: 20px;
        display: inline-block;
    }

    /* Красная точка перед номером строки */
    .highlighted-line .line-number::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 12px;
        height: 12px;
        background-color: red;
        border-radius: 50%;
    }

    .trace-item {
        margin: 5px 0;
        padding: 5px;
        background: #3a3a3a;
        border-left: 3px solid #27ae60;
        word-wrap: break-word;
        overflow-wrap: anywhere;
        white-space: normal;
    }
    <?php if ($environment === 'production'): ?>

    body {
        background: rgb(17, 17, 17);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        font-family: 'Arial', sans-serif;
        text-align: center;
        color: #ffffff;
    }

    .rotate {
        animation: animateGears 3.5s linear infinite;
    }

    .ccw {
        animation-direction: reverse;
    }

    @keyframes animateGears {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    .error-container {
        background-color: #262626;
        padding: 30px;
        border-radius: 10px;
        max-width: 800px;
        width: 100%;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s ease-in-out;
    }

    h1 {
        font-size: 28px;
        color: #1abc9c;
        font-weight: bold;
        margin-bottom: 20px;
        text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.5);
    }

    .error-message {
        font-size: 18px;
        color: #bdc3c7;
        margin-top: 15px;
        line-height: 1.6;
    }

    p {
        font-size: 16px;
        color: #95a5a6;
        margin-top: 10px;
        line-height: 1.6;
    }

    a {
        display: inline-block;
        margin-top: 20px;
        padding: 12px 24px;
        font-size: 16px;
        color: #ffffff;
        background-color: #1abc9c;
        text-decoration: none;
        border-radius: 6px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transition: background 0.3s, transform 0.2s;
    }

    a:hover {
        background-color: #16a085;
        transform: translateY(-2px);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    <?php endif; ?>

    @media (max-width: 768px) {
        h1 {
            font-size: 26px;
        }

        .error-message {
            font-size: 18px;
        }

        .error-trace {
            font-size: 12px;
        }
    }
</style>