<?php
// database/migrations/2024_01_01_000003_create_claims_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('claimant_id')->constrained('users')->onDelete('cascade');
            $table->string('item_name');
            $table->text('proof_of_ownership');
            $table->text('special_characteristics');
            $table->string('phone');
            $table->string('email');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->boolean('notification_read')->default(false); // FITUR UNGGULAN: tracking notif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};