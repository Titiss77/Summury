<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the application tables. Authentication and migration tables are
 * supplied by CodeIgniter Shield / the migration runner.
 */
class CreateApplicationTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nom' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('header', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_header' => ['type' => 'INT', 'unsigned' => true],
            'nom' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('id_header');
        $this->forge->addForeignKey('id_header', 'header', 'id', 'CASCADE', 'CASCADE', 'division_id_header_foreign');
        $this->forge->createTable('division', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_user' => ['type' => 'INT', 'unsigned' => true],
            'id_division' => ['type' => 'INT', 'unsigned' => true],
            'sous_categorie' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'titre' => ['type' => 'VARCHAR', 'constraint' => 255],
            'titre_original' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Aucun'],
            'is_public' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'description' => ['type' => 'TEXT', 'null' => true],
            'date_sortie' => ['type' => 'DATETIME', 'null' => true],
            'image' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'lien' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'link_status' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'saison' => ['type' => 'INT', 'null' => true],
            'total_saisons' => ['type' => 'INT', 'null' => true],
            'episode' => ['type' => 'INT', 'null' => true],
            'total_episodes' => ['type' => 'INT', 'null' => true],
            'episode_global' => ['type' => 'INT', 'null' => true],
            'total_episodes_global' => ['type' => 'INT', 'null' => true],
            'position' => ['type' => 'INT', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('id_user');
        $this->forge->addKey('is_public');
        $this->forge->addKey('id_division');
        $this->forge->addForeignKey('id_division', 'division', 'id', 'CASCADE', 'CASCADE', 'item_id_division_foreign');
        $this->forge->addForeignKey('id_user', 'users', 'id', 'CASCADE', 'CASCADE', 'item_id_user_foreign');
        $this->forge->createTable('item', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'original_item_id' => ['type' => 'INT', 'unsigned' => true],
            'id_user' => ['type' => 'INT', 'unsigned' => true],
            'titre' => ['type' => 'VARCHAR', 'constraint' => 255],
            'sous_categorie' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50],
            'image' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'lien' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'episode' => ['type' => 'INT', 'null' => true],
            'total_episodes' => ['type' => 'INT', 'null' => true],
            'saison' => ['type' => 'INT', 'null' => true],
            'total_saisons' => ['type' => 'INT', 'null' => true],
            'position' => ['type' => 'INT', 'default' => 0],
            'date_sortie' => ['type' => 'DATETIME', 'null' => true],
            'revision_status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'pending'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('original_item_id');
        $this->forge->addKey('id_user');
        $this->forge->addForeignKey('id_user', 'users', 'id', 'CASCADE', 'CASCADE', 'item_revisions_id_user_foreign');
        $this->forge->addForeignKey('original_item_id', 'item', 'id', 'CASCADE', 'CASCADE', 'item_revisions_original_item_id_foreign');
        $this->forge->createTable('item_revisions', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'item_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'type' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'pending'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('item_id');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('item_id', 'item', 'id', 'CASCADE', 'CASCADE', 'reports_item_id_foreign');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'reports_user_id_foreign');
        $this->forge->createTable('reports', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 255],
            'details' => ['type' => 'TEXT', 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE', 'audit_logs_user_id_foreign');
        $this->forge->createTable('audit_logs', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'task_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'last_run' => ['type' => 'DATETIME'],
            'item_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'titre' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'url_testee' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'code_erreur' => ['type' => 'INT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('item_id');
        $this->forge->addForeignKey('item_id', 'item', 'id', 'CASCADE', 'CASCADE', 'cron_logs_item_id_foreign');
        $this->forge->createTable('cron_logs', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'domain' => ['type' => 'VARCHAR', 'constraint' => 255],
            'regex_episode' => ['type' => 'VARCHAR', 'constraint' => 255],
            'indicateurs_page_invalide' => ['type' => 'TEXT', 'null' => true],
            'indicateurs_lecteur' => ['type' => 'TEXT', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('sites_config', true);

        $this->forge->addField([
            'nom' => ['type' => 'VARCHAR', 'constraint' => 50],
            'ordre' => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addKey('nom', true);
        $this->forge->createTable('statuts', true);

    }

    public function down(): void
    {
        foreach ([
            'statuts', 'sites_config', 'cron_logs',
            'audit_logs', 'reports', 'item_revisions', 'item', 'division', 'header',
        ] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}