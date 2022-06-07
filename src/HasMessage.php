<?php

namespace Illuminatech\ModelRules;

/**
 * HasMessage manages setup of custom validation error message for the validation rule.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait HasMessage
{
    /**
     * @var string|null error message to be used on validation failure.
     */
    private $message;

    /**
     * Sets the error message to be used on validation failure.
     *
     * @param string|null $message validation error message.
     * @return static self reference.
     */
    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Returns the error message to be used on validation failure.
     *
     * @return string validation error message.
     */
    public function getMessage()
    {
        if ($this->message === null) {
            $this->message = $this->defaultMessage();
        }

        return $this->message;
    }

    /**
     * Alias of {@see setMessage()}
     *
     * @param string|null $message validation error message.
     * @return static self reference.
     */
    public function withMessage(?string $message): self
    {
        return $this->setMessage($message);
    }

    /**
     * Defines the default validation error message.
     *
     * @return string default error message.
     */
    abstract protected function defaultMessage(): string;
}