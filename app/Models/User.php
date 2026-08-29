<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Observers\UserObserver;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\Email\Concerns\InteractsWithEmailAuthentication;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[ObservedBy([UserObserver::class])]
#[Fillable(['name', 'email', 'password', 'created_by', 'updated_by', 'deleted_by', 'has_email_authentication'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasEmailAuthentication, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, InteractsWithEmailAuthentication, Notifiable, SoftDeletes;

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
            'has_email_authentication' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function ddos()
    {
        return $this->hasMany(Ddo::class);
    }

    public function createdDdos()
    {
        return $this->hasMany(Ddo::class, 'created_by');
    }

    public function updatedDdos()
    {
        return $this->hasMany(Ddo::class, 'updated_by');
    }

    public function deletedDdos()
    {
        return $this->hasMany(Ddo::class, 'deleted_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
