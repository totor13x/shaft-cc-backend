<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            TagSeeder::class,

            UserSeeder::class,
            PointshopSeeder::class,
            BalanceSeeder::class,

            RolePermissionSeeder::class,

            LockSeeder::class,
            LockMugaSeeder::class,

            OnlineSeeder::class,
            ChatSeeder::class,
            TtsSeeder::class,

            UserIpSeeder::class,
            UserPhotoSeeder::class,
            UserTrackSeeder::class,
        ]);
    }
}
