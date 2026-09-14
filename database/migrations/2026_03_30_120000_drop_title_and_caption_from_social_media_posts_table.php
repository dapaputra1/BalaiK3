<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_media_posts', function (Blueprint $table) {
            if (Schema::hasColumn('social_media_posts', 'title')) {
                $table->dropColumn('title');
            }

            if (Schema::hasColumn('social_media_posts', 'caption')) {
                $table->dropColumn('caption');
            }
        });
    }

    public function down(): void
    {
        Schema::table('social_media_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('social_media_posts', 'title')) {
                $table->string('title')->nullable()->after('platform');
            }

            if (!Schema::hasColumn('social_media_posts', 'caption')) {
                $table->longText('caption')->nullable()->after('title');
            }
        });
    }
};
