<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /** بەڕێوەبەری سەرەکی — دەستی بە هەموو شتێک دەگات. */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /** بەرپرسی کۆگا — تەنها مەخزەن، نرخ نابینێت. */
    public function isStorekeeper(): bool
    {
        return $this->hasRole('storekeeper');
    }

    /** ئایا ئەم بەکارهێنەرە بۆی هەیە نرخ و پارە ببینێت؟ */
    public function canSeeMoney(): bool
    {
        return $this->isAdmin();
    }
}
