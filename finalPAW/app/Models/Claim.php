<?php
// app/Models/Claim.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    protected $fillable = [
        'item_id', 'claimant_id', 'item_name', 'proof_of_ownership',
        'special_characteristics', 'phone', 'email',
        'status', 'admin_note', 'notification_read',
        'deleted_by_user_at',
    ];

    protected $casts = [
        'deleted_by_user_at' => 'datetime',
    ];

    // Scope: exclude soft-deleted by user
    public function scopeVisible($query)
    {
        return $query->whereNull('deleted_by_user_at');
    }

    // Relasi
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function claimant()
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    // Badge status
    public function statusBadge(): array
    {
        return match($this->status) {
            'pending'  => ['label' => 'Menunggu Verifikasi', 'class' => 'badge-warning'],
            'approved' => ['label' => 'Disetujui', 'class' => 'badge-success'],
            'claimed'  => ['label' => 'Sudah Diambil', 'class' => 'badge-gray'],
            'rejected' => ['label' => 'Ditolak', 'class' => 'badge-danger'],
            default    => ['label' => 'Unknown', 'class' => 'badge-gray'],
        };
    }
}