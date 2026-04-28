<?php

namespace App\Models;

use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable implements CanResetPasswordContract, MustVerifyEmailContract
{
    use CanResetPassword;
    use MustVerifyEmail;
    use Notifiable;

    protected $table = 'clients';

    protected $fillable = [
        'type',
        'identification',
        'login_email',
        'password',
        'is_active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
        ];
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    /** Email used for verification (we use login_email). */
    public function getEmailForVerification(): string
    {
        return (string) $this->login_email;
    }

    /** Email used for password reset (we use login_email). */
    public function getEmailForPasswordReset(): string
    {
        return (string) $this->login_email;
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Current cart (order with kind=cart) for this client, if any. */
    public function cart(): HasMany
    {
        return $this->hasMany(Order::class)->where('kind', 'cart');
    }

    public function personalizedSolutions(): HasMany
    {
        return $this->hasMany(PersonalizedSolution::class);
    }

    /** Primary contact (name, surname, phone) for buyer profile. */
    public function primaryContact(): HasMany
    {
        return $this->hasMany(ClientContact::class)->where('is_primary', true);
    }
}
