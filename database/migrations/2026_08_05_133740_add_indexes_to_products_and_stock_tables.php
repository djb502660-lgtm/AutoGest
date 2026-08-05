<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Products indexes
        $this->addIndexIfNotExists('products', ['category_id', 'is_active']);
        $this->addIndexIfNotExists('products', ['brand_id', 'is_active']);
        $this->addIndexIfNotExists('products', ['sku']);

        // Stock movements indexes
        $this->addIndexIfNotExists('stock_movements', ['product_id', 'type']);
        $this->addIndexIfNotExists('stock_movements', ['created_at']);

        // Chatbot messages indexes
        $this->addIndexIfNotExists('chatbot_messages', ['session_id']);
        $this->addIndexIfNotExists('chatbot_messages', ['created_at']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'is_active']);
            $table->dropIndex(['brand_id', 'is_active']);
            $table->dropIndex(['sku']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'type']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('chatbot_messages', function (Blueprint $table) {
            $table->dropIndex(['session_id']);
            $table->dropIndex(['created_at']);
        });
    }

    private function addIndexIfNotExists($table, $columns)
    {
        $indexName = is_array($columns)
            ? $table.'_'.implode('_', $columns).'_index'
            : $table.'_'.$columns.'_index';

        $indexExists = DB::select(
            'SELECT COUNT(*) as count FROM information_schema.statistics
             WHERE table_schema = DATABASE()
             AND table_name = ?
             AND index_name = ?',
            [$table, $indexName]
        );

        if ($indexExists[0]->count == 0) {
            Schema::table($table, function (Blueprint $table) use ($columns) {
                $table->index($columns);
            });
        }
    }
};
