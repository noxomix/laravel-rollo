<?php

namespace Noxomix\LaravelRollo\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class RolloAuthorizationException extends HttpException
{
    /**
     * Create a new authorization exception.
     *
     * @param string $message
     * @param int $statusCode
     * @param \Throwable|null $previous
     * @param array $headers
     * @param int $code
     */
    public function __construct(
        string $message = 'This action is unauthorized.',
        int $statusCode = 403,
        \Throwable $previous = null,
        array $headers = [],
        int $code = 0
    ) {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}