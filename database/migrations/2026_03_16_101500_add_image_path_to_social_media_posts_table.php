<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('social_media_posts', 'image_path')) {
            Schema::table('social_media_posts', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('post_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('social_media_posts', 'image_path')) {
            Schema::table('social_media_posts', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
    }
};
