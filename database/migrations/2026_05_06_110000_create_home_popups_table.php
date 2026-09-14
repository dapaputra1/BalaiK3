<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_popups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('desktop_image_path');
            $table->string('mobile_image_path');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $desktopPath = 'images/popup.png';
        $mobilePath = 'images/popup_mobile.png';

        if (Schema::hasTable('app_settings')) {
            $settings = DB::table('app_settings')
                ->whereIn('key', ['home_popup_desktop_image', 'home_popup_mobile_image'])
                ->pluck('value', 'key');

            $desktopPath = (string) ($settings['home_popup_desktop_image'] ?? $desktopPath);
            $mobilePath = (string) ($settings['home_popup_mobile_image'] ?? $mobilePath);
        }

        DB::table('home_popups')->insert([
            'name' => 'Popup Home 1',
            'desktop_image_path' => $desktopPath !== '' ? $desktopPath : 'images/popup.png',
            'mobile_image_path' => $mobilePath !== '' ? $mobilePath : 'images/popup_mobile.png',
            'is_active' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('home_popups');
    }
};
