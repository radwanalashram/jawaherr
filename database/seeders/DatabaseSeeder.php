<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Admin user
        DB::table('users')->updateOrInsert(
            ['username' => 'admin'],
            [
                'id' => (string) Str::uuid(),
                'email' => 'admin@example.com',
                'password' => Hash::make('ChangeMe123!'),
                'role' => 'admin',
                'full_name' => 'Administrator',
                'phone' => '+201000000000',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Currencies
        DB::table('currencies')->updateOrInsert(['code' => 'EGP'], [
            'id' => (string) Str::uuid(),
            'name' => 'Egyptian Pound',
            'symbol' => 'EGP',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('currencies')->updateOrInsert(['code' => 'USD'], [
            'id' => (string) Str::uuid(),
            'name' => 'US Dollar',
            'symbol' => 'USD',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Item units
        $pcsId = (string) Str::uuid();
        DB::table('item_units')->updateOrInsert(['code' => 'pcs'], [
            'id' => $pcsId,
            'name' => 'قطعة',
            'code' => 'pcs',
            'conversion_factor' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Items sample
        DB::table('items')->updateOrInsert(['sku' => 'ITEM001'], [
            'id' => (string) Str::uuid(),
            'sku' => 'ITEM001',
            'name' => 'قلم حبر أسود',
            'description' => 'قلم حبر أسود عادي',
            'barcode' => '1234567890123',
            'unit_id' => $pcsId,
            'purchase_price' => 5.00,
            'sale_price' => 10.00,
            'cost' => 5.00,
            'stock_total' => 500,
            'track_stock' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('items')->updateOrInsert(['sku' => 'ITEM002'], [
            'id' => (string) Str::uuid(),
            'sku' => 'ITEM002',
            'name' => 'دفتر مذكرات A4',
            'description' => 'دفتر 80 صفحة',
            'barcode' => '2345678901234',
            'unit_id' => $pcsId,
            'purchase_price' => 12.00,
            'sale_price' => 25.00,
            'cost' => 12.00,
            'stock_total' => 200,
            'track_stock' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}