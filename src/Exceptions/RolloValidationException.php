<?php

namespace Noxomix\LaravelRollo\Exceptions;

class RolloValidationException extends \InvalidArgumentException
{
    /**
     * The validation errors.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Create a new validation exception.
     *
     * @param string $message
     * @param array $errors
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "", array $errors = [], int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * Get the validation errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Create a validation exception with multiple errors.
     *
     * @param array $errors
     * @return static
     */
    public static function withErrors(array $errors): self
    {
        $message = "Validation failed: " . implode(', ', array_values($errors));
        return new static($message, $errors);
    }
}