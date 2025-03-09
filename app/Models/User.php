<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enum\RoleCode;
use Filament\Notifications\Auth\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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

    public function canAccessPanel(Panel $panel): bool
    {
        $panel_id = $panel->getId(); // "admin" or "merchant"
        if($panel_id === 'admin'){
            $role = $this->roles()->where('role_id', RoleCode::admin)->first();
            return !is_null($role);
        }else if($panel_id === 'merchant'){
            // $role = $this->roles()->where('role_id', RoleCode::merchant)->first();
            // return !is_null($role);
            return true;
        }
        
        return false;
    }

    public function roles(): BelongsToMany{
        return $this->belongsToMany(Role::class);
    }

    public function addresses(): HasMany{
        return $this->hasMany(Address::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $url = url("/admin/reset-password?token={$token}");

        $this->notify(new ResetPassword($token));
    }
}
