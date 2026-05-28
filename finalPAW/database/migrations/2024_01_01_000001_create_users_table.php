<?php
// database/migrations/2024_01_01_000001_create_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nim')->nullable()->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'mahasiswa'])->default('mahasiswa');
            $table->string('angkatan', 4)->nullable();
            $table->string('fakultas')->nullable()->default('Ilmu Komputer');
            $table->string('jurusan')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};