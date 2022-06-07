<?php

namespace Illuminatech\ModelRules;

/**
 * HasMessage
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait HasMessage
{
    /**
     * @var string|null
     */
    private $message;

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage()
    {
        if ($this->message === null) {
            $this->message = $this->defaultMessage();
        }

        return $this->message;
    }

    /**
     * Defines the default validation error message.
     *
     * @return string default error message.
     */
    abstract protected function defaultMessage(): string;
}