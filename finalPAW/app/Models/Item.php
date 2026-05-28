<?php
// app/Models/Item.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Item extends Model
{
    protected $fillable = [
        'reported_by', 'name', 'description', 'category',
        'location_found', 'found_date', 'photo', 'contact_info',
        'status', 'report_code', 'deleted_by_user_at',
    ];

    protected $casts = [
        'found_date' => 'date',
        'deleted_by_user_at' => 'datetime',
    ];

    // Generate kode unik saat creating
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($item) {
            $item->report_code = 'LF-' . strtoupper(Str::random(6)) . '-' . date('Y');
        });
    }

    // Relasi
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    public function activeClaim()
    {
        return $this->hasOne(Claim::class)->whereIn('status', ['pending', 'approved']);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    // Badge warna berdasarkan status
    public function statusBadge(): array
    {
        return match($this->status) {
            'pending'        => ['label' => 'Menunggu Verifikasi', 'class' => 'badge-warning'],
            'available'      => ['label' => 'Tersedia', 'class' => 'badge-success'],
            'in_claim'       => ['label' => 'Sedang Diklaim', 'class' => 'badge-orange'],
            'waiting_pickup' => ['label' => 'Menunggu Pengambilan', 'class' => 'badge-info'],
            'claimed'        => ['label' => 'Sudah Diklaim', 'class' => 'badge-gray'],
            default          => ['label' => 'Unknown', 'class' => 'badge-gray'],
        };
    }

    // Apakah bisa diklaim
    public function isClaimable(): bool
    {
        return $this->status === 'available';
    }

    // Photo URL
    public function photoUrl(): string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : asset('images/placeholder.png');
    }
}