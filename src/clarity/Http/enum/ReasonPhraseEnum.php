<?php

declare(strict_types=1);

namespace framework\clarity\Http\enum;

enum ReasonPhraseEnum: int
{
    case OK = 200;
    case Created = 201;
    case NoContent = 204;
    case BadRequest = 400;
    case NotFound = 404;
    case InternalServerError = 500;

    /**
     * @param int|bool $statusCode
     * @return string
     */
    public static function getReasonPhrase(int|bool $statusCode): string
    {
        return match ($statusCode) {
            self::OK->value => 'OK',
            self::Created->value => 'Created',
            self::NoContent->value => 'No Content',
            self::BadRequest->value => 'Bad Request',
            self::NotFound->value => 'Not Found',
            self::InternalServerError->value => 'Internal Server Error',
            default => ''
        };
    }
}
