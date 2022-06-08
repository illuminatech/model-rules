<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2022 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ModelRules;

/**
 * Exists checks if requested model exists or not.
 *
 * For example:
 *
 * ```
 * $request->validate([
 *     'category_id' => [
 *         'required',
 *         'integer',
 *         Exists::new(Category::class),
 *     ],
 * ]);
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Exists extends AbstractRule
{
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
        return $this->parseMessage($this->getMessage());
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultMessage(): string
    {
        return 'validation.exists';
    }
}