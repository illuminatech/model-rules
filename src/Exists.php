<?php

namespace Illuminatech\ModelRules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Exists
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Exists implements Rule
{
    /**
     * @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object data source.
     */
    protected $query;

    /**
     * @var string|null name of the attribute (column) to compare validation value against.
     */
    protected $attribute;

    /**
     * @var \Illuminate\Database\Eloquent\Model|object|null last queried model.
     */
    protected $model;

    /**
     * Constructor.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object|string $source data source.
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
     * {@inheritdoc}
     */
    public function passes($attribute, $value): bool
    {
        $this->model = null;

        $query = clone $this->query;

        if ($this->attribute === null) {
            $query->whereKey($value);
        } else {
            $query->where($this->attribute, $value);
        }

        $this->model = $query->first();

        return $this->model !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function message()
    {
        return __('validation.exists');
    }

    public function getModel(): ?object
    {
        return $this->model;
    }
}