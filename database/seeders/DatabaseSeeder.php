<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for MongoDB.
     */
    public function run(): void
    {
        // Set Faker locale to English (en_US)
        $faker = Faker::create('en_US');

        // ------------------------------------------------------------
        // 1. Fetch existing Admin from MongoDB
        // ------------------------------------------------------------
        $existingAdmin = DB::connection('mongodb')->table('admins')->first();

        if (!$existingAdmin) {
            $this->command->error("Please create at least one Admin via Postman/Register first!");
            return;
        }

        // Safely extract string ID using object arrow syntax (->)
        $adminId = (string) ($existingAdmin->_id ?? $existingAdmin->id ?? null);

        // ------------------------------------------------------------
        // 2. Clear old data from collections
        // ------------------------------------------------------------
        DB::connection('mongodb')->table('categories')->delete();
        DB::connection('mongodb')->table('brands')->delete();
        DB::connection('mongodb')->table('products')->delete();
        DB::connection('mongodb')->table('invoices')->delete();

        // ------------------------------------------------------------
        // 3. Seed Collection: CATEGORIES
        // ------------------------------------------------------------
        $categoryNames = ['Antibiotics', 'Pain Relievers', 'Vitamins', 'Supplements', 'Cough & Cold', 'Cardiovascular', 'Dermatology', 'Diabetes Care', 'First Aid', 'Baby Care'];
        $categoryIds = [];
        
        foreach ($categoryNames as $catName) {
            $catId = bin2hex(random_bytes(12)); 
            $categoryIds[] = $catId;

            DB::connection('mongodb')->table('categories')->insert([
                '_id' => $catId,
                'category_name' => $catName,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // ------------------------------------------------------------
        // 4. Seed Collection: BRANDS
        // ------------------------------------------------------------
        $brandNames = ['PharmaCorp', 'BioHealth', 'MedLife', 'GSK', 'Pfizer', 'Sanofi', 'Novartis', 'Abbott', 'Bayer', 'AstraZeneca'];
        $brandIds = [];

        foreach ($brandNames as $bName) {
            $bId = bin2hex(random_bytes(12));
            $brandIds[] = $bId;

            DB::connection('mongodb')->table('brands')->insert([
                '_id' => $bId,
                'brand_name' => $bName,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // ------------------------------------------------------------
        // 5. Seed Collection: PRODUCTS
        // ------------------------------------------------------------
        $productIds = [];
        $productPics = [
            'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?q=80&w=500&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1616679911721-ebd6e4149688?q=80&w=500&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1471864190281-a93a3070b6de?q=80&w=500&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1576091160550-2173dba999ef?q=80&w=500&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1607619056574-7b8d304f3c6f?q=80&w=500&auto=format&fit=crop'
        ];

        $medicineNames = [
            'Paracetamol 500mg', 'Amoxicillin 250mg', 'Vitamin C 1000mg', 'Ibuprofen 400mg', 'Cetirizine 10mg',
            'Metformin 500mg', 'Atorvastatin 20mg', 'Loratadine 10mg', 'Omeprazole 20mg', 'Amlodipine 5mg'
        ];

        for ($i = 1; $i <= 50; $i++) {
            $prodId = bin2hex(random_bytes(12));
            $productIds[] = $prodId;

            $price = $faker->randomFloat(2, 2, 60);
            $discount = $faker->randomElement([0.00, 0.00, 0.50, 1.00, 1.50]);

            DB::connection('mongodb')->table('products')->insert([
                '_id' => $prodId,
                'product_name' => $faker->randomElement($medicineNames) . ' (Batch ' . $i . ')',
                'product_price' => $price,
                'product_discount' => $discount,
                'product_status' => $faker->randomElement(['available', 'available', 'out_of_stock']),
                'product_manufactured_date' => $faker->date('Y-m-d', 'now'),
                'product_expired_date' => $faker->date('Y-m-d', '+3 years'),
                'product_detail' => $faker->sentence(12),
                'product_pic' => $faker->randomElement($productPics),
                'product_pic_public_id' => 'products/prod_' . Str::random(10),
                'category_id' => $faker->randomElement($categoryIds), 
                'brand_id' => $faker->randomElement($brandIds),
                'admin_id' => $adminId, 
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // ------------------------------------------------------------
        // 6. Seed Collection: INVOICES (Matching your exact properties)
        // ------------------------------------------------------------
        for ($i = 1; $i <= 40; $i++) {
            $invId = bin2hex(random_bytes(12));

            DB::connection('mongodb')->table('invoices')->insert([
                '_id'               => $invId,
                'payment_id'        => bin2hex(random_bytes(12)), // Generates unique hex string for payment_id
                'admin_id'          => $adminId,
                'invoice_number'    => 'INV-' . strtoupper(Str::random(8)) . '-' . $i, 
                'invoice_create_at' => $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
                'created_at'        => now(), 
                'updated_at'        => now(),
            ]);
        }

        $this->command->info('Database seeded successfully! Fields perfectly match your schema layout.');
    }
}