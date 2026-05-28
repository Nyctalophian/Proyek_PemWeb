<?php
// database/migrations/2024_01_01_000002_create_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->string('category'); // Elektronik, Aksesori, Dokumen, Pakaian, Lainnya
            $table->string('location_found');
            $table->date('found_date');
            $table->string('photo')->nullable();
            $table->string('contact_info');
            $table->enum('status', ['pending', 'available', 'in_claim', 'waiting_pickup', 'claimed'])
                  ->default('pending');
            $table->string('report_code')->unique(); // Kode laporan unik
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};