<?php

namespace framework\clarity\Http;

use AllowDynamicProperties;
use Exception;
use framework\clarity\Http\enum\ReasonPhraseEnum;
use RuntimeException;

class JsonResponse extends Response
{
    /**
     * @param array $data
     * @param $status
     */
    public function __construct(
        array $data = [],
        $status = 200
    ) {
        try {
            parent::__construct();

            $this->body->setIsWritable(true);

            $content = json_encode($data);

            $this->body->write($content);

            $this->body->rewind();

            $this->setStatusCode($status);

            $this->setReasonPhrase(ReasonPhraseEnum::getReasonPhrase($status));

            $this->setHeader('Content-Type', 'application/json; charset=UTF-8');

            $this->setHeader('Content-Length', (string)$this->body->getSize());

        } catch (Exception $e) {

            throw new RuntimeException("Failed to render JSON response", 500, $e);
        }

        return $this;
    }
}