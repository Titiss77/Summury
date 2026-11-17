<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLinkCheckedAt extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('item', [
            'link_checked_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
                'after' => 'link_status',
            ],
        ]);
        $this->db->query('CREATE INDEX idx_item_link_checked_at ON item (link_checked_at)');
    }

    public function down(): void
    {
        $this->db->query('DROP INDEX idx_item_link_checked_at ON item');
        $this->forge->dropColumn('item', 'link_checked_at');
    }
}
