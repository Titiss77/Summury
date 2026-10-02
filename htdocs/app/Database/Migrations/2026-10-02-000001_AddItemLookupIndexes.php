<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddItemLookupIndexes extends Migration
{
    public function up(): void
    {
        $this->forge->addKey(['id_user', 'deleted_at', 'status'], false, false, 'idx_item_user_deleted_status');
        $this->forge->addKey(['id_user', 'deleted_at', 'id_division', 'position'], false, false, 'idx_item_user_group_order');
        $this->forge->addKey(['is_public', 'deleted_at', 'id_division', 'position'], false, false, 'idx_item_public_group_order');
        $this->forge->addKey(['revision_status', 'original_item_id'], false, false, 'idx_revision_status_item');

        $this->forge->processIndexes('item');
        $this->forge->processIndexes('item_revisions');
    }

    public function down(): void
    {
        $this->forge->dropKey('item', 'idx_item_user_deleted_status');
        $this->forge->dropKey('item', 'idx_item_user_group_order');
        $this->forge->dropKey('item', 'idx_item_public_group_order');
        $this->forge->dropKey('item_revisions', 'idx_revision_status_item');
    }
}
