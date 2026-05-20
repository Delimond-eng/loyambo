<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE inventaires MODIFY COLUMN status ENUM('pending', 'closed', 'cancelled') DEFAULT 'pending'");
        } else {
            Schema::table('inventaires', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE inventaires MODIFY COLUMN status ENUM('pending', 'closed') DEFAULT 'pending'");
        }
    }
};
