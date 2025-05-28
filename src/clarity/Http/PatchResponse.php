<?php

namespace framework\clarity\Http;

class PatchResponse extends JsonResponse
{
    public function __construct(array $data = ['status' => 'patched'])
    {
        parent::__construct($data, 200); // 200 OK
    }
}