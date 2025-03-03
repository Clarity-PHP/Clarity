<?php

namespace framework\clarity\Http;

final readonly class StreamParams
{
    public const STREAM_TO_MEMORY = 'php://memory';
    public const STREAM_TO_TEMP = 'php://temp';
    public const STREAM_THRESHOLD = 1024 * 1024; // 1MB
}