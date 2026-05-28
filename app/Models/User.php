<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'nim', 'email', 'password', 'role',
        'angkatan', 'fakultas', 'jurusan', 'phone', 'address', 'avatar',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    // Relasi
    public function items()
    {
        return $this->hasMany(Item::class, 'reported_by');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class, 'claimant_id');
    }

    public function appNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Helper
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function unreadNotificationsCount(): int
    {
        return $this->appNotifications()->where('is_read', false)->count();
    }
}