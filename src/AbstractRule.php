<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2022 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ModelRules;

use Illuminate\Contracts\Validation\Rule;

/**
 * AbstractRule is a base model rule class.
 *
 * It defines core features:
 *
 *  - setup of model search query for the validation rule;
 *  - handle validation rule instantiation;
 *  - manage setup of custom validation error message for the validation rule;
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
abstract class AbstractRule implements Rule
{
    /**
     * @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object data source.
     */
    private $query;

    /**
     * @var string|null name of the attribute (column) to compare validation value against.
     */
    protected $attribute;

    /**
     * @var string|null error message to be used on validation failure.
     */
    private $message;

    /**
     * Constructor.
     *
     * @param \Illuminate\Database\Eloquent\Builder|object|string $source data source.
     * @param string|null $attribute name of the attribute (column) to compare validation value against, if not set - model key will be used.
     */
    public function __construct($source, ?string $attribute = null)
    {
        if (is_object($source)) {
            $this->query = $source;
        } elseif (is_string($source)) {
            $this->query = $source::query();
        } else {
            throw new \InvalidArgumentException('Unsupported source type: ' . gettype($source));
        }

        $this->attribute = $attribute;
    }

    /**
     * Creates new validation rule instance.
     * @see __construct()
     *
     * @param mixed ...$args constructor arguments.
     * @return static new validation rule instance.
     */
    public static function new(...$args): self
    {
        return new static(...$args);
    }

    /**
     * Creates new query builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder|object query builder.
     */
    protected function newQuery(): object
    {
        return clone $this->query;
    }

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

    /**
     * {@inheritdoc}
     */
    public function message()
    {
        return $this->getMessage();
    }
}