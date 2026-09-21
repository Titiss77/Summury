<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        // Désactiver temporairement les vérifications de clés étrangères si nécessaire
        // (utile si tu vides/TRUNCATE tes tables dans tes seeders)
        // $this->db->disableForeignKeyChecks();

        // 1. Tables indépendantes / parents
        $this->call('UserSeeder'); // Dépend de Shield (si tu utilises Shield pour la gestion des utilisateurs)
        $this->call('StatutSeeder');
        $this->call('HeaderSeeder');
        $this->call('SiteConfigSeeder');

        // 2. Tables enfants (dépendent des parents)
        $this->call('DivisionSeeder'); // Dépend de Header
        $this->call('ItemSeeder');  // Dépend de Users et Division

        // Réactiver les vérifications
        // $this->db->enableForeignKeyChecks();
    }
}
