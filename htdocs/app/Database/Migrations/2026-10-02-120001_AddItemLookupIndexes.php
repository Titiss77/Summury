<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddItemLookupIndexes extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('item', 'idx_item_user_deleted_status', ['id_user', 'deleted_at', 'status']);
        $this->addIndexIfMissing('item', 'idx_item_user_group_order', ['id_user', 'deleted_at', 'id_division', 'position']);
        $this->addIndexIfMissing('item', 'idx_item_public_group_order', ['is_public', 'deleted_at', 'id_division', 'position']);
        $this->addIndexIfMissing('item_revisions', 'idx_revision_status_item', ['revision_status', 'original_item_id']);
    }

    public function down(): void
    {
        $this->forge->dropKey('item', 'idx_item_user_deleted_status');
        $this->forge->dropKey('item', 'idx_item_user_group_order');
        $this->forge->dropKey('item', 'idx_item_public_group_order');
        $this->forge->dropKey('item_revisions', 'idx_revision_status_item');
    }

    /**
     * Avoid failing on databases where an index was created manually or by a
     * previous partially completed migration run.
     *
     * @param list<string> $columns
     */
    private function addIndexIfMissing(string $table, string $name, array $columns): void
    {
        if (isset($this->db->getIndexData($table)[$name])) {
            return;
        }

        $this->forge->addKey($columns, false, false, $name);
        $this->forge->processIndexes($table);
    }
}