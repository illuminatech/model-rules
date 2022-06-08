<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2022 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ModelRules;

/**
 * Unique
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Unique extends AbstractRule
{
    use HasModel;

    /**
     * @var mixed value of the model key attribute, which should be ignored during uniqueness check.
     */
    protected $ignore;

    /**
     * @var string|null name of the attribute, to which the {@see $ignore} value should be applied.
     */
    protected $ignoreAttribute;

    /**
     * {@inheritdoc}
     */
    public function passes($attribute, $value): bool
    {
        $this->model = null;

        $query = $this->newQuery();

        if ($this->attribute === null) {
            $query->whereKey($value);
        } else {
            $query->where($this->attribute, $value);
        }

        if ($this->ignore !== null) {
            if ($this->ignoreAttribute === null) {
                $query->whereKeyNot($value);
            } else {
                $query->where($this->ignoreAttribute, '!=', $this->ignore);
            }
        }

        $this->model = $query->first();

        return $this->model === null;
    }

    /**
     * {@inheritdoc}
     */
    public function message()
    {
        return $this->parseMessage($this->getMessage());
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultMessage(): string
    {
        return 'validation.unique';
    }

    /**
     * Ignore the given model ID during the unique check.
     *
     * @param \Illuminate\Database\Eloquent\Model|object|mixed $modelOrKey model instance or key value.
     * @param string|null $keyAttribute key attribute name, if not set - model key name will be used.
     * @return static self reference.
     */
    public function ignore($modelOrKey, ?string $keyAttribute = null): self
    {
        if (is_object($modelOrKey)) {
            $this->ignoreAttribute = $keyAttribute ?? $modelOrKey->getKeyName();
            $this->ignore = $modelOrKey->{$this->ignoreAttribute};

            return $this;
        }

        $this->ignoreAttribute = $keyAttribute;
        $this->ignore = $modelOrKey;

        return $this;
    }
}