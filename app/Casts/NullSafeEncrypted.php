<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Drop-in replacement for the built-in 'encrypted' cast that returns null instead of
 * throwing DecryptException when the stored value was encrypted with a different APP_KEY.
 *
 * On decryption failure the cast also calls $model->recordDecryptionError($key) when the
 * model uses the TracksDecryptionErrors trait, so controllers can detect the issue and
 * include a _decryption_error flag in their API response.
 *
 * Fix: set APP_PREVIOUS_KEYS in .env to the old key value, or ensure APP_KEY matches
 * the key that was active when the data was originally encrypted.
 */
class NullSafeEncrypted implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            if (method_exists($model, 'recordDecryptionError')) {
                $model->recordDecryptionError($key);
            }

            return null;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value !== null ? encrypt($value) : null;
    }
}
