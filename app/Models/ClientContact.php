<?php

namespace App\Models;

use App\Casts\NullSafeEncrypted;
use App\Models\Concerns\TracksDecryptionErrors;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContact extends Model
{
    use TracksDecryptionErrors;
    protected $table = 'client_contacts';

    protected $fillable = [
        'client_id',
        'name',
        'surname',
        'phone',
        'phone2',
        'email',
        'is_primary',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'phone' => NullSafeEncrypted::class,
            'phone2' => NullSafeEncrypted::class,
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
