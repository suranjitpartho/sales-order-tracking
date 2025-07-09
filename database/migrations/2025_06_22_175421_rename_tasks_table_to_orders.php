<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
    public function up(): void
    {
        DB::statement('RENAME TABLE tasks TO orders');
    }

    public function down(): void
    {
        DB::statement('RENAME TABLE orders TO tasks');
    }
};


