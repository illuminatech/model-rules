<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2022 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ModelRules;

/**
 * HasQuery manages setup of model search query for the validation rule.
 * Since it is the core feature, this trait handles validation rule instantiation as well.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait HasQuery
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
}