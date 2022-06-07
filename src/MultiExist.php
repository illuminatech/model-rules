<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2022 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ModelRules;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;

/**
 * MultiExist
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class MultiExist implements Rule
{
    use HasQuery;
    use HasMessage;
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

        $value = array_unique($value);

        $query = $this->newQuery();

        if ($this->attribute === null) {
            $query->whereKey($value);
        } else {
            $query->whereIn($this->attribute, $value);
        }

        $this->models = $query->get();

        return $this->models->count() === count($value);
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
        return 'validation.exists';
    }
}