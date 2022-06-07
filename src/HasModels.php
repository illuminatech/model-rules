<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2022 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ModelRules;

use Illuminate\Support\Collection;

/**
 * HasModels handles multiple models storage and retrieval for the validation rule.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait HasModels
{
    /**
     * @var \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[] last queried models.
     */
    protected $models;

    /**
     * Returns the collection of last models queried during validation.
     *
     * @var \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[] last queried models.
     */
    public function getModels(): Collection
    {
        if ($this->models === null) {
            $this->models = new Collection();
        }

        return $this->models;
    }
}