<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('statuslog');
    }

    public function down(): void
    {
        Schema::create('statuslog', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['Pending', 'Shipped', 'Delivered', 'Cancelled']);
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();
        });
    }
};
