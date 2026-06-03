<?php

namespace Database\Seeders;

use App\Models\DistributionChannel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'manager']);
        Role::firstOrCreate(['name' => 'staff']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@synapse.local'],
            [
                'name' => 'System Admin',
                'first_name' => 'System',
                'last_name' => 'Admin',
                'phone' => null,
                'password' => bcrypt('password'),
            ],
        );

        $admin->assignRole($adminRole);

        $channels = [
            ['name' => 'YouTube', 'slug' => 'youtube'],
            ['name' => 'Instagram', 'slug' => 'instagram'],
            ['name' => 'LinkedIn', 'slug' => 'linkedin'],
            ['name' => 'Facebook', 'slug' => 'facebook'],
            ['name' => 'Website', 'slug' => 'website'],
        ];

        foreach ($channels as $channel) {
            DistributionChannel::firstOrCreate(['slug' => $channel['slug']], $channel);
        }
    }
}
