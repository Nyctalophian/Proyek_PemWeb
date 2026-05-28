<?php
// app/Models/Notification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'title', 'message', 'type',
        'notifiable_id', 'notifiable_type', 'is_read',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable()
    {
        return $this->morphTo();
    }

    // Buat notifikasi baru
    public static function create_for(User $user, string $type, string $title, string $message, Model $notifiable): self
    {
        return static::create([
            'user_id'         => $user->id,
            'title'           => $title,
            'message'         => $message,
            'type'            => $type,
            'notifiable_id'   => $notifiable->id,
            'notifiable_type' => get_class($notifiable),
            'is_read'         => false,
        ]);
    }
}