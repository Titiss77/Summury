<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $usersData = [
            [
                'id'   => 1,
                'username' => 'SuperAdmin',
                'status' => null,
                'status_message' => null,
                'active' => 1,
                'last_active' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null,
            ],
            [
                'id'   => 2,
                'username' => 'Admin',
                'status' => null,
                'status_message' => null,
                'active' => 1,
                'last_active' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null,
            ],
            [
                'id'   => 3,
                'username' => 'User',
                'status' => null,
                'status_message' => null,
                'active' => 1,
                'last_active' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null,
            ]
        ];

        $auth_identitiesData = [
            [
                'id'   => 1,
                'user_id' => 1,
                'type' => 'email_password',
                'name' => null,
                'secret' => 'superadmin@gmail.com',
                'secret2' => password_hash('superadmin@gmail.com', PASSWORD_DEFAULT),
                'expires' => null,
                'extra' => null,
                'force_reset' => 0,
                'last_used_at' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'   => 2,
                'user_id' => 2,
                'type' => 'email_password',
                'name' => null,
                'secret' => 'admin@gmail.com',
                'secret2' => password_hash('admin@gmail.com', PASSWORD_DEFAULT),
                'expires' => null,
                'extra' => null,
                'force_reset' => 0,
                'last_used_at' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'   => 3,
                'user_id' => 3,
                'type' => 'email_password',
                'name' => null,
                'secret' => 'user@gmail.com',
                'secret2' => password_hash('user@gmail.com', PASSWORD_DEFAULT),
                'expires' => null,
                'extra' => null,
                'force_reset' => 0,
                'last_used_at' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $auth_groups_usersData = [
            [
                'id'   => 1,
                'user_id' => 1,
                'group' => 'superadmin',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'   => 2,
                'user_id' => 2,
                'group' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'   => 3,
                'user_id' => 3,
                'group' => 'user',
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ];

        
        $this->db->table('users')->insertBatch($usersData);
        $this->db->table('auth_identities')->insertBatch($auth_identitiesData);
        $this->db->table('auth_groups_users')->insertBatch($auth_groups_usersData);
    }
}