<?php

namespace framework\clarity\Http;

class UpdateResponse extends JsonResponse
{
    public function __construct(array $data = ['status' => 'updated'])
    {
        parent::__construct($data, 200);
    }
}
