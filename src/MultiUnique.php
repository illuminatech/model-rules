<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2022 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ModelRules;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * MultiUnique checks if requested models specified as attribute values array are unique or not.
 *
 * For example:
 *
 * ```
 * $request->validate([
 *     'tags' => [
 *         'array',
 *         MultiUnique::new(Tag::class, 'name'),
 *     ],
 * ]);
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class MultiUnique extends AbstractRule
{
    use HasModels;

    /**
     * {@inheritdoc}
     */
    public function passes($attribute, $value)
    {
        $this->models = new Collection();

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        }

        if (!is_array($value)) {
            return false;
        }

        if ($value != array_unique($value)) {
            return false;
        }

        $query = $this->newQuery();

        if ($this->attribute === null) {
            $query->whereKey($value);
        } else {
            $query->whereIn($this->attribute, $value);
        }

        $this->models = $query->get();

        return $this->models->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function message()
    {
        return $this->getMessage();
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultMessage(): string
    {
        return 'validation.unique';
    }
}