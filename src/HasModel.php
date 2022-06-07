<?php

namespace Illuminatech\ModelRules;

/**
 * HasModel handles model storage and retrieval for the validator.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait HasModel
{
    /**
     * @var \Illuminate\Database\Eloquent\Model|object|null last queried model.
     */
    protected $model;

    /**
     * Returns the last model queried during validation.
     *
     * @return \Illuminate\Database\Eloquent\Model|object|null last queried model.
     */
    public function getModel(): ?object
    {
        return $this->model;
    }
}