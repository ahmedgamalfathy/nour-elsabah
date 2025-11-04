<?php

namespace Database\Seeders;

use App\Models\StaticPage\StaticPage;
use Illuminate\Database\Seeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\User\UserSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\Client\ClientSeeder;
use Database\Seeders\Roles\RolesAndPermissionsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductCategorySeeder::class,
            ProductMediaSeeder::class,
            ClientSeeder::class,
            OrderSeeder::class,
            StaticPageSeeder::class
        ]);

    }
}
