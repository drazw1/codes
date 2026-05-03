<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Demo admin user (for auth) ─────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@pharmacy.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );

        // ── Categories ─────────────────────────────────────────
        $categories = [
            ['category_name' => 'Analgesics',     'description' => 'Pain relief medications'],
            ['category_name' => 'Antibiotics',    'description' => 'Bacterial infection treatments'],
            ['category_name' => 'Antivirals',     'description' => 'Viral infection treatments'],
            ['category_name' => 'Vitamins',       'description' => 'Nutritional supplements'],
            ['category_name' => 'Cardiovascular', 'description' => 'Heart and blood pressure medications'],
        ];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['category_name' => $cat['category_name']], $cat);
        }

        // ── Suppliers ──────────────────────────────────────────
        $suppliersData = [
            ['supplier_name' => 'MedSource Ltd',       'contact_person' => 'John Pharma',   'email' => 'john@medsource.com',   'phone' => '+1-555-0101', 'address' => '12 Medicine Ave, NY'],
            ['supplier_name' => 'PharmaPlus Co',       'contact_person' => 'Sarah Wells',   'email' => 'sarah@pharmaplus.com', 'phone' => '+1-555-0202', 'address' => '45 Health Blvd, LA'],
            ['supplier_name' => 'Global Rx Supply',    'contact_person' => 'Mike Torres',   'email' => 'mike@globalrx.com',    'phone' => '+1-555-0303', 'address' => '78 Wellness St, TX'],
            ['supplier_name' => 'CarePharm Wholesale', 'contact_person' => 'Lisa Chang',    'email' => 'lisa@carepharm.com',   'phone' => '+1-555-0404', 'address' => '23 Supply Rd, FL'],
        ];
        foreach ($suppliersData as $sup) {
            Supplier::firstOrCreate(['email' => $sup['email']], $sup);
        }

        $analgesics    = Category::where('category_name', 'Analgesics')->first();
        $antibiotics   = Category::where('category_name', 'Antibiotics')->first();
        $antivirals    = Category::where('category_name', 'Antivirals')->first();
        $vitamins      = Category::where('category_name', 'Vitamins')->first();
        $cardiovascular= Category::where('category_name', 'Cardiovascular')->first();

        $sup1 = Supplier::where('email', 'john@medsource.com')->first();
        $sup2 = Supplier::where('email', 'sarah@pharmaplus.com')->first();
        $sup3 = Supplier::where('email', 'mike@globalrx.com')->first();
        $sup4 = Supplier::where('email', 'lisa@carepharm.com')->first();

        // ── Medicines + Many-to-Many pivot ─────────────────────
        $medicines = [
            [
                'data'      => ['medicine_name' => 'Paracetamol 500mg', 'category_id' => $analgesics->category_id,    'price' => 3.50,  'stock' => 200, 'prescription_required' => 'NO'],
                'suppliers' => [
                    $sup1->supplier_id => ['unit_cost' => 1.20, 'quantity' => 500, 'last_supplied_at' => '2024-11-01'],
                    $sup2->supplier_id => ['unit_cost' => 1.35, 'quantity' => 300, 'last_supplied_at' => '2024-10-15'],
                ],
            ],
            [
                'data'      => ['medicine_name' => 'Ibuprofen 400mg',   'category_id' => $analgesics->category_id,    'price' => 5.00,  'stock' => 150, 'prescription_required' => 'NO'],
                'suppliers' => [
                    $sup1->supplier_id => ['unit_cost' => 2.00, 'quantity' => 200, 'last_supplied_at' => '2024-11-10'],
                ],
            ],
            [
                'data'      => ['medicine_name' => 'Amoxicillin 250mg', 'category_id' => $antibiotics->category_id,   'price' => 12.00, 'stock' => 45,  'prescription_required' => 'YES'],
                'suppliers' => [
                    $sup2->supplier_id => ['unit_cost' => 6.50, 'quantity' => 100, 'last_supplied_at' => '2024-10-20'],
                    $sup3->supplier_id => ['unit_cost' => 6.80, 'quantity' => 80,  'last_supplied_at' => '2024-09-30'],
                ],
            ],
            [
                'data'      => ['medicine_name' => 'Ciprofloxacin 500mg','category_id'=> $antibiotics->category_id,   'price' => 18.50, 'stock' => 30,  'prescription_required' => 'YES'],
                'suppliers' => [
                    $sup2->supplier_id => ['unit_cost' => 9.00, 'quantity' => 60,  'last_supplied_at' => '2024-11-05'],
                ],
            ],
            [
                'data'      => ['medicine_name' => 'Acyclovir 400mg',   'category_id' => $antivirals->category_id,    'price' => 22.00, 'stock' => 8,   'prescription_required' => 'YES'],
                'suppliers' => [
                    $sup3->supplier_id => ['unit_cost' => 11.00,'quantity' => 40,  'last_supplied_at' => '2024-08-15'],
                    $sup4->supplier_id => ['unit_cost' => 10.50,'quantity' => 50,  'last_supplied_at' => '2024-09-01'],
                ],
            ],
            [
                'data'      => ['medicine_name' => 'Vitamin C 1000mg',  'category_id' => $vitamins->category_id,      'price' => 6.75,  'stock' => 300, 'prescription_required' => 'NO'],
                'suppliers' => [
                    $sup1->supplier_id => ['unit_cost' => 2.50, 'quantity' => 600, 'last_supplied_at' => '2024-11-12'],
                    $sup4->supplier_id => ['unit_cost' => 2.70, 'quantity' => 400, 'last_supplied_at' => '2024-10-25'],
                ],
            ],
            [
                'data'      => ['medicine_name' => 'Vitamin D3 5000IU', 'category_id' => $vitamins->category_id,      'price' => 9.99,  'stock' => 250, 'prescription_required' => 'NO'],
                'suppliers' => [
                    $sup1->supplier_id => ['unit_cost' => 4.00, 'quantity' => 300, 'last_supplied_at' => '2024-11-01'],
                ],
            ],
            [
                'data'      => ['medicine_name' => 'Amlodipine 5mg',    'category_id' => $cardiovascular->category_id,'price' => 15.00, 'stock' => 5,   'prescription_required' => 'YES'],
                'suppliers' => [
                    $sup4->supplier_id => ['unit_cost' => 7.00, 'quantity' => 30,  'last_supplied_at' => '2024-07-20'],
                ],
            ],
            [
                'data'      => ['medicine_name' => 'Atenolol 50mg',     'category_id' => $cardiovascular->category_id,'price' => 11.25, 'stock' => 60,  'prescription_required' => 'YES'],
                'suppliers' => [
                    $sup3->supplier_id => ['unit_cost' => 5.00, 'quantity' => 80,  'last_supplied_at' => '2024-10-10'],
                    $sup4->supplier_id => ['unit_cost' => 5.20, 'quantity' => 60,  'last_supplied_at' => '2024-09-15'],
                ],
            ],
            [
                'data'      => ['medicine_name' => 'Metformin 500mg',   'category_id' => $cardiovascular->category_id,'price' => 8.00,  'stock' => 0,   'prescription_required' => 'YES'],
                'suppliers' => [
                    $sup2->supplier_id => ['unit_cost' => 3.50, 'quantity' => 0,   'last_supplied_at' => '2024-06-01'],
                ],
            ],
            [
                'data'      => ['medicine_name' => 'Aspirin 81mg',      'category_id' => $analgesics->category_id,    'price' => 4.25,  'stock' => 180, 'prescription_required' => 'NO'],
                'suppliers' => [
                    $sup1->supplier_id => ['unit_cost' => 1.80, 'quantity' => 400, 'last_supplied_at' => '2024-11-08'],
                    $sup2->supplier_id => ['unit_cost' => 1.90, 'quantity' => 350, 'last_supplied_at' => '2024-10-30'],
                ],
            ],
            [
                'data'      => ['medicine_name' => 'Azithromycin 250mg','category_id' => $antibiotics->category_id,   'price' => 25.00, 'stock' => 20,  'prescription_required' => 'YES'],
                'suppliers' => [
                    $sup3->supplier_id => ['unit_cost' => 12.50,'quantity' => 50,  'last_supplied_at' => '2024-09-20'],
                ],
            ],
        ];

        foreach ($medicines as $item) {
            $med = Medicine::firstOrCreate(
                ['medicine_name' => $item['data']['medicine_name']],
                $item['data']
            );
            // sync() prevents duplicate pivot rows on re-seed
            $med->suppliers()->sync($item['suppliers']);
        }
    }
}
