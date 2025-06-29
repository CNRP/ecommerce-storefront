<?php

// database/seeders/DefaultVendorSeeder.php

namespace Database\Seeders;

use App\Models\User\User;
use App\Models\User\Vendor;
use Illuminate\Database\Seeder;

class DefaultVendorSeeder extends Seeder
{
    public function run()
    {
        // Create default admin user
        $user = User::create([
            'name' => 'Store Owner',
            'email' => 'admin@yourstore.com',
            'password' => bcrypt('password'),
            'type' => 'vendor',
        ]);

        // Create default vendor
        Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Your Store Name',
            'slug' => 'main-store',
            'description' => 'Main store description',
            'status' => 'approved',
            'commission_rate' => 0.00, // No commission for own store
        ]);
    }
}
