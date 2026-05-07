<?php

namespace App\Models\Concerns;

/**
 * Lets a model record which encrypted attributes failed decryption.
 * Used together with NullSafeEncrypted cast so controllers can detect
 * a mismatched APP_KEY and include a _decryption_error flag in their response.
 */
trait TracksDecryptionErrors
{
    protected array $decryptionErrors = [];

    public function recordDecryptionError(string $key): void
    {
        $this->decryptionErrors[] = $key;
    }

    public function hasDecryptionErrors(): bool
    {
        return ! empty($this->decryptionErrors);
    }

    public function getDecryptionErrors(): array
    {
        return $this->decryptionErrors;
    }
}
