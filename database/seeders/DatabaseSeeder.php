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
        Role::firstOrCreate(['name' => 'superadmin']);
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
        $admin->assignRole('superadmin');

        $channels = [
            ['name' => 'YouTube', 'slug' => 'youtube'],
            ['name' => 'Spotify', 'slug' => 'spotify'],
            ['name' => 'Audiomack', 'slug' => 'audiomack'],
            ['name' => 'Instagram', 'slug' => 'instagram'],
            ['name' => 'LinkedIn', 'slug' => 'linkedin'],
            ['name' => 'Facebook', 'slug' => 'facebook'],
            ['name' => 'Website', 'slug' => 'website'],
        ];

        foreach ($channels as $channel) {
            DistributionChannel::firstOrCreate(['slug' => $channel['slug']], $channel);
        }

        // Seed default chart of accounts for the admin
        $accounts = [
            ['code' => '1000', 'name' => 'Cash', 'type' => 'asset', 'currency' => 'GHS'],
            ['code' => '1010', 'name' => 'Bank Account', 'type' => 'asset', 'currency' => 'GHS'],
            ['code' => '1020', 'name' => 'Mobile Money', 'type' => 'asset', 'currency' => 'GHS'],
            ['code' => '4000', 'name' => 'Consulting Revenue', 'type' => 'revenue', 'currency' => 'GHS'],
            ['code' => '4100', 'name' => 'Product Sales', 'type' => 'revenue', 'currency' => 'GHS'],
            ['code' => '4200', 'name' => 'Other Income', 'type' => 'revenue', 'currency' => 'GHS'],
            ['code' => '5000', 'name' => 'Rent Expense', 'type' => 'expense', 'currency' => 'GHS'],
            ['code' => '5010', 'name' => 'Utilities', 'type' => 'expense', 'currency' => 'GHS'],
            ['code' => '5020', 'name' => 'Software Subscriptions', 'type' => 'expense', 'currency' => 'GHS'],
            ['code' => '5030', 'name' => 'Marketing', 'type' => 'expense', 'currency' => 'GHS'],
            ['code' => '5040', 'name' => 'Travel', 'type' => 'expense', 'currency' => 'GHS'],
        ];

        foreach ($accounts as $account) {
            \App\Models\LedgerAccount::firstOrCreate(
                ['user_id' => $admin->id, 'code' => $account['code']],
                [
                    'user_id' => $admin->id,
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'currency' => $account['currency'],
                ]
            );
        }
    }
}

