<?php

namespace framework\clarity\Logger;

use DateTimeImmutable;
use DateTimeZone;
use Error;
use Exception;

class LogContextHandler
{
    /**
     * @param LogContext $storage
     * @return void
     */
    public function handle(LogContext $storage): void
    {
        $data = $this->prepareResult($storage);

        $storage->result = [
            'context' => $data['context'],
            // TODO: Реализация категорий?
            'category' => $data['category'] ?? '',
            'level' => $data['level'],
            'level_name' => $data['level_name'],
            'action' => $data['action'],
            'datetime' => $data['datetime'],
            'timestamp' => $data['timestamp'],
            'userId' => $data['user_id'],
            'ip' => $data['ip'],
            'real_ip' => $data['real_ip'],
            'x_debug_tag' => $data['x_debug_tag'],
            'message' => $data['message'],
            'exception' => $data['exception'],
            'extras' => $storage->result['extras'] ?? [],
        ];
    }

    /**
     * @param LogContext $storage
     * @return array
     */
    private function prepareResult(LogContext $storage): array
    {
        $result = [];

        $result['context'] = isset($storage->result['context']) === true
            ? $storage->result['context'] : null;

        list($result['level'], $result['level_name'], $result['timestamp'], $result['message']) = $storage->data;

        $result['exception'] = null;

        if (
            $result['message'] instanceof Exception === true
            ||
            (class_exists(Error::class) && $result['message'] instanceof Error)
        ) {
            $result['exception'] = [
                'file' => $result['message']->getFile(),
                'line' => $result['message']->getLine(),
                'code' => $result['message']->getCode(),
                'trace' => explode(PHP_EOL, $result['message']->getTraceAsString()),
            ];

            $result['message'] = $result['message']->getMessage();
        }

        $result['datetime'] = $this->safeCreateDateTimeImmutable('now', 'UTC');

        if ($result['datetime'] !== null) {

            $result['datetime'] = $result['datetime']->format('Y-m-d\TH:i:s.uP');

            $result['timestamp'] = (new DateTimeImmutable)->format('Y-m-d\TH:i:s.uP');
        }

        $result['real_ip'] = null;

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) === true) {
            $realIpList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            $result['real_ip'] = array_shift($realIpList);
        }

        $result['action'] = null;

        if (isset($storage->request) === true) {
            $result['action'] = $storage->request->getUri()->getPath();
        }

        // TODO: определение userId
        $result['user_id'] = null;

        $result['ip'] = isset($_SERVER['HTTP_X_REAL_IP']) === true ? $_SERVER['HTTP_X_REAL_IP'] : null;

        $result['x_debug_tag'] = $storage->tagStorage->getTag();

        return $result;
    }

    /**
     * @param string $time
     * @param string $timezone
     * @return DateTimeImmutable|null
     */
    private function safeCreateDateTimeImmutable(string $time, string $timezone): ?DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($time, new DateTimeZone($timezone));
        } catch (Exception $e) {
            return null;
        }
    }
}