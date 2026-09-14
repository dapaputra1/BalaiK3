<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('package_key')->nullable()->after('price');
            $table->string('package_name')->nullable()->after('package_key');
            $table->decimal('package_price', 12, 2)->nullable()->after('package_name');
            $table->json('package_details')->nullable()->after('package_price');
            $table->index('package_key');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex(['package_key']);
            $table->dropColumn([
                'package_key',
                'package_name',
                'package_price',
                'package_details',
            ]);
        });
    }
};
