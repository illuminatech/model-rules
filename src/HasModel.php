<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2022 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ModelRules;

use Illuminate\Support\Facades\Lang;

/**
 * HasModel handles model storage and retrieval for the validation rule.
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

    /**
     * Translates the message, parsing model attributes as placeholders.
     *
     * @param string $message raw message.
     * @param array<string, mixed> $replace the replacement placeholder values.
     * @return string parsed message.
     */
    protected function parseMessage(string $message, array $replace = []): string
    {
        if ($this->model !== null) {
            foreach ($this->model->getAttributes() as $attribute => $value) {
                if (!is_scalar($value)) {
                    continue;
                }

                $replace['model_' . $attribute] = $value;
            }
        }

        return Lang::get($message, $replace);
    }
}