<?php
declare(strict_types=1);

final class ApiException extends RuntimeException
{
    /**
     * @param array<string,string> $errors
     */
    public function __construct(
        string $message,
        public readonly int $httpStatus = 400,
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }
}
