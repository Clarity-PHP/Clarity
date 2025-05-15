<?php

namespace framework\clarity\Http;

class CreateResponse extends JsonResponse
{
    public function __construct(array $data = ['status' => 'created'])
    {
        parent::__construct($data, 201);
    }
}