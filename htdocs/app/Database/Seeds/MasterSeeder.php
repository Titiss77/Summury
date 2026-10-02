<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/** Runs the application's seeders in dependency order. */
class MasterSeeder extends Seeder
{
    public function run(): void
    {
        $this->call('ReferenceDataSeeder');
        $this->call('SuperAdminSeeder');
        $this->call('DemoDataSeeder');
    }
}
