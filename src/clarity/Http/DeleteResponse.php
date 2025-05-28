<?php

namespace framework\clarity\Http;

class DeleteResponse extends JsonResponse
{
    public function __construct(array $data = ['status' => 'deleted'])
    {
        parent::__construct($data, 204);
    }
}