<?php

namespace Illuminatech\ModelRules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Lang;

/**
 * Exists
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Exists implements Rule
{
    use HasQuery;
    use HasMessage;
    use HasModel;

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

        $this->model = $query->first();

        return $this->model !== null;
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
        return Lang::get('validation.exists');
    }
}