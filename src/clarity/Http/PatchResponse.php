<?php

namespace framework\clarity\Http;

class PatchResponse extends JsonResponse
{
    /**
     * @param array $data
     * @param int $status
     */
    public function __construct(array $data = ['status' => 'patched'], int $status = 200)
    {
        parent::__construct($data, $status); // 200 OK
    }
}