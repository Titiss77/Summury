<?php declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGlobalEpisodesToItem extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('item', [
            'episode_global' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'total_episodes_global' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('item', ['episode_global', 'total_episodes_global']);
    }
}