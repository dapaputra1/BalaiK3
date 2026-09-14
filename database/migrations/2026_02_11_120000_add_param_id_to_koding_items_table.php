<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kodings')) {
            Schema::create('kodings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permohonan_id')->constrained('permohonans')->cascadeOnDelete();
                $table->string('status')->default('draft');
                $table->timestamp('sent_to_prepanalisa_at')->nullable();
                $table->integer('created_by')->nullable();
                $table->integer('updated_by')->nullable();
                $table->timestamps();
            });

            Schema::table('kodings', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('koding_items')) {
            Schema::create('koding_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('koding_id')->constrained('kodings')->cascadeOnDelete();
                $table->foreignId('pengujian_dokumen_id')->constrained('pengujian_dokumen')->cascadeOnDelete();
                $table->foreignId('pengujian_dokumen_parameter_id')
                    ->nullable()
                    ->constrained('pengujian_dokumen_parameters')
                    ->nullOnDelete();
                $table->string('kode')->nullable();
                $table->timestamps();

                $table->unique(['koding_id', 'pengujian_dokumen_parameter_id'], 'koding_items_unique_param');
            });
            return;
        }

        if (Schema::hasColumn('koding_items', 'pengujian_dokumen_id') && DB::getDriverName() === 'mysql') {
            $fkKoding = DB::selectOne("
                SELECT CONSTRAINT_NAME as name
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'koding_items'
                  AND COLUMN_NAME = 'koding_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");
            if (!empty($fkKoding?->name)) {
                DB::statement("ALTER TABLE `koding_items` DROP FOREIGN KEY `{$fkKoding->name}`");
            }

            $fkRow = DB::selectOne("
                SELECT CONSTRAINT_NAME as name
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'koding_items'
                  AND COLUMN_NAME = 'pengujian_dokumen_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");
            if (!empty($fkRow?->name)) {
                DB::statement("ALTER TABLE `koding_items` DROP FOREIGN KEY `{$fkRow->name}`");
            }

            $idxRows = DB::select("
                SELECT INDEX_NAME as name
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'koding_items'
                  AND COLUMN_NAME = 'pengujian_dokumen_id'
            ");
            foreach ($idxRows as $idx) {
                if (!empty($idx->name) && $idx->name !== 'PRIMARY' && $idx->name !== 'koding_items_unique_doc') {
                    DB::statement("ALTER TABLE `koding_items` DROP INDEX `{$idx->name}`");
                }
            }
        }

        Schema::table('koding_items', function (Blueprint $table) {
            if (!Schema::hasColumn('koding_items', 'pengujian_dokumen_parameter_id')) {
                $table->foreignId('pengujian_dokumen_parameter_id')
                    ->nullable()
                    ->after('pengujian_dokumen_id')
                    ->constrained('pengujian_dokumen_parameters')
                    ->nullOnDelete();
            }
        });

        if (DB::getDriverName() === 'mysql') {
            $hasUnique = DB::selectOne("
                SELECT INDEX_NAME as name
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'koding_items'
                  AND INDEX_NAME = 'koding_items_unique_doc'
                LIMIT 1
            ");
            if (!empty($hasUnique?->name)) {
                DB::statement("ALTER TABLE `koding_items` DROP INDEX `koding_items_unique_doc`");
            }
        }

        Schema::table('koding_items', function (Blueprint $table) {
            if (Schema::hasColumn('koding_items', 'pengujian_dokumen_parameter_id')) {
                $table->unique(['koding_id', 'pengujian_dokumen_parameter_id'], 'koding_items_unique_param');
            }
        });

        Schema::table('koding_items', function (Blueprint $table) {
            if (Schema::hasColumn('koding_items', 'pengujian_dokumen_id')) {
                $table->foreign('pengujian_dokumen_id')
                    ->references('id')
                    ->on('pengujian_dokumen')
                    ->cascadeOnDelete();
            }
        });

        if (DB::getDriverName() === 'mysql') {
            $hasKodingFk = DB::selectOne("
                SELECT CONSTRAINT_NAME as name
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'koding_items'
                  AND COLUMN_NAME = 'koding_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");
            if (empty($hasKodingFk?->name)) {
                Schema::table('koding_items', function (Blueprint $table) {
                    if (Schema::hasColumn('koding_items', 'koding_id')) {
                        $table->foreign('koding_id')
                            ->references('id')
                            ->on('kodings')
                            ->cascadeOnDelete();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('koding_items')) {
            return;
        }

        if (Schema::hasColumn('koding_items', 'pengujian_dokumen_id') && DB::getDriverName() === 'mysql') {
            $fkRow = DB::selectOne("
                SELECT CONSTRAINT_NAME as name
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'koding_items'
                  AND COLUMN_NAME = 'pengujian_dokumen_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");
            if (!empty($fkRow?->name)) {
                DB::statement("ALTER TABLE `koding_items` DROP FOREIGN KEY `{$fkRow->name}`");
            }
        }

        Schema::table('koding_items', function (Blueprint $table) {
            $table->dropUnique('koding_items_unique_param');
        });

        Schema::table('koding_items', function (Blueprint $table) {
            $table->unique(['koding_id', 'pengujian_dokumen_id'], 'koding_items_unique_doc');
        });

        Schema::table('koding_items', function (Blueprint $table) {
            if (Schema::hasColumn('koding_items', 'pengujian_dokumen_parameter_id')) {
                $table->dropConstrainedForeignId('pengujian_dokumen_parameter_id');
            }
        });

        Schema::table('koding_items', function (Blueprint $table) {
            if (Schema::hasColumn('koding_items', 'pengujian_dokumen_id')) {
                $table->foreign('pengujian_dokumen_id')
                    ->references('id')
                    ->on('pengujian_dokumen')
                    ->cascadeOnDelete();
            }
        });
    }
};
