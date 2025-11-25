<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Illuminate\Support\Str;
use Filament\Panel\Concerns\HasAvatars;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;

use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;
    /** @use HasFactory<\Database\Factories\UserFactory> */

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'is_admin',
        'is_agent',
        'is_agency',
        'is_doctor',
        'is_subagent',
        'is_patient',
        'is_designer',
        'is_business_admin',
        'is_superAdmin',
        'is_accountManagers',
        'code_agency',
        'link_agency',
        'link_agent',
        'agency_type',
        'departament',
        'birthday_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Restriccion para acceso al panel administrativo
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'viveadmin') {
            // return str_ends_with($this->email, '@vivepluss.com') && $this->is_white_label_company && $this->agency_type === 'MASTER';
            // return str_ends_with($this->email, '@vivepluss.com');
            return true;
        }

    }
}