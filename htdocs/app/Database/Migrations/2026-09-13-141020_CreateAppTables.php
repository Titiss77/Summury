<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAppTables extends Migration
{
    public function up(): void
    {
        // 1. Table `header`
        $this->forge->addField([
            'id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nom'  => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('header');

        // 2. Table `division`
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_header' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nom'       => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('id_header', 'header', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('division');

        // 3. Table `statuts`
        $this->forge->addField([
            'nom'   => ['type' => 'VARCHAR', 'constraint' => 50],
            'ordre' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('nom');
        $this->forge->createTable('statuts');

        // 4. Table `sites_config`
        $this->forge->addField([
            'id'                        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'domain'                    => ['type' => 'VARCHAR', 'constraint' => 255],
            'regex_episode'             => ['type' => 'VARCHAR', 'constraint' => 255],
            'indicateurs_page_invalide' => ['type' => 'TEXT', 'null' => true],
            'indicateurs_lecteur'       => ['type' => 'TEXT', 'null' => true],
            'is_active'                 => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('sites_config');

        // 5. Table `item`
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_user'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // Lien avec Shield
            'id_division'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'sous_categorie' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'titre'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'titre_original' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Aucun'],
            'is_public'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0], // 0: Privé, 1: Public, 2: En attente
            'description'    => ['type' => 'TEXT', 'null' => true],
            'date_sortie'    => ['type' => 'DATETIME', 'null' => true],
            'image'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'lien'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'link_status'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'saison'         => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'total_saisons'  => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'episode'        => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'total_episodes' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'position'       => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'     => ['type' => 'DATETIME', 'null' => true], // Soft deletes
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('id_user', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_division', 'division', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('item');

        // 6. Table `item_revisions`
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'original_item_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_user'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'titre'            => ['type' => 'VARCHAR', 'constraint' => 255],
            'sous_categorie'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'           => ['type' => 'VARCHAR', 'constraint' => 50],
            'image'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'lien'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'description'      => ['type' => 'TEXT', 'null' => true],
            'episode'          => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'total_episodes'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'saison'           => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'total_saisons'    => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'position'         => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'date_sortie'      => ['type' => 'DATETIME', 'null' => true],
            'revision_status'  => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'pending'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('original_item_id', 'item', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_user', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('item_revisions');

        // 7. Table `audit_logs`
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'action'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'details'    => ['type' => 'TEXT', 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('audit_logs');

        // 8. Table `cron_logs`
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'task_name'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'last_run'    => ['type' => 'DATETIME'],
            'item_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'titre'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'url_testee'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'code_erreur' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        // FK optionnelle, mais pratique si l'item est supprimé ça nettoie les logs de cron :
        $this->forge->addForeignKey('item_id', 'item', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cron_logs');

        // 9. Table `reports`
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'item_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'type'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'pending'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('item_id', 'item', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reports');
    }

    public function down(): void
    {
        $this->forge->dropTable('reports', true);
        $this->forge->dropTable('cron_logs', true);
        $this->forge->dropTable('audit_logs', true);
        $this->forge->dropTable('item_revisions', true);
        $this->forge->dropTable('item', true);
        $this->forge->dropTable('sites_config', true);
        $this->forge->dropTable('statuts', true);
        $this->forge->dropTable('division', true);
        $this->forge->dropTable('header', true);
    }
}