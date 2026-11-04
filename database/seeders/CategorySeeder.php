<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Pengeluaran (expense)
            [
                'name' => 'Potongan kerja (BPJS)',
                'type' => 'expense',
                'amount' => 400000,
                'description' => 'Kategori pengeluaran untuk potongan kerja BPJS',
                'is_default' => true,
            ],
            [
                'name' => 'Kosan',
                'type' => 'expense',
                'amount' => 1400000,
                'description' => 'Kategori pengeluaran biaya sewa kosan bulanan',
                'is_default' => true,
            ],
            [
                'name' => 'Motor',
                'type' => 'expense',
                'amount' => 500000,
                'description' => 'Kategori pengeluaran kebutuhan dan perawatan motor',
                'is_default' => true,
            ],
            [
                'name' => 'Kartu Kredit BRI Touch',
                'type' => 'expense',
                'amount' => 450000,
                'description' => 'Kategori pengeluaran tagihan kredit BRI Touch',
                'is_default' => true,
            ],
            [
                'name' => 'Kartu Kredit BRI Tokopedia',
                'type' => 'expense',
                'amount' => 950000,
                'description' => 'Kategori pengeluaran tagihan kartu kredit BRI Tokopedia',
                'is_default' => true,
            ],
            [
                'name' => 'Makan',
                'type' => 'expense',
                'amount' => 1000000,
                'description' => 'Kategori pengeluaran makan bulanan Rp. 250.000/minggu',
                'is_default' => true,
            ],
            [
                'name' => 'Lain lain',
                'type' => 'expense',
                'amount' => 500000,
                'description' => 'Kategori pengeluaran kebutuhan lain-lain',
                'is_default' => true,
            ],
            [
                'name' => 'Nabung bibit',
                'type' => 'expense',
                'amount' => 1000000,
                'description' => 'Kategori alokasi tabungan investasi Bibit',
                'is_default' => true,
            ],
            [
                'name' => 'Pegangan',
                'type' => 'expense',
                'amount' => 500000,
                'description' => 'Kategori uang pegangan bulanan',
                'is_default' => true,
            ],
            [
                'name' => 'Mama',
                'type' => 'expense',
                'amount' => 0,
                'description' => 'Kategori pengeluaran untuk Mama',
                'is_default' => false,
            ],
            [
                'name' => 'Aziz Kosan',
                'type' => 'expense',
                'amount' => 0,
                'description' => 'Kategori pengeluaran kosan Aziz',
                'is_default' => false,
            ],
            [
                'name' => 'Aziz Makan',
                'type' => 'expense',
                'amount' => 750000,
                'description' => 'Kategori pengeluaran uang makan Aziz',
                'is_default' => true,
            ],
            [
                'name' => 'Aziz Jajan',
                'type' => 'expense',
                'amount' => 249000,
                'description' => 'Kategori pengeluaran uang jajan Aziz',
                'is_default' => true,
            ],
            [
                'name' => 'Aziz SPP 3 Bulan (1)',
                'type' => 'expense',
                'amount' => 800000,
                'description' => 'Kategori pengeluaran SPP Aziz (1)',
                'is_default' => true,
            ],
            [
                'name' => 'Aziz SPP 3 Bulan (2)',
                'type' => 'expense',
                'amount' => 800000,
                'description' => 'Kategori pengeluaran SPP Aziz (2)',
                'is_default' => true,
            ],
            [
                'name' => 'Aziz SPP 3 Bulan (3)',
                'type' => 'expense',
                'amount' => 800000,
                'description' => 'Kategori pengeluaran SPP Aziz (3)',
                'is_default' => true,
            ],
            [
                'name' => 'Save Money',
                'type' => 'expense',
                'amount' => 500000,
                'description' => 'Kategori tabungan Save Money',
                'is_default' => false,
            ],

            // Pemasukan (income)
            [
                'name' => 'Salary 2026',
                'type' => 'income',
                'amount' => 8980000,
                'description' => 'Kategori pemasukan gaji',
                'is_default' => true,
            ],

            // Lembur (income, is_default = false)
            [
                'name' => 'Lembur 1 jam',
                'type' => 'income',
                'amount' => 65376,
                'description' => '1.5 × 43.584',
                'is_default' => false,
            ],
            [
                'name' => 'Lembur 1.5 jam',
                'type' => 'income',
                'amount' => 108960,
                'description' => '1.5 + 0.5×2',
                'is_default' => false,
            ],
            [
                'name' => 'Lembur 2 jam',
                'type' => 'income',
                'amount' => 152544,
                'description' => '1.5 + 1×2',
                'is_default' => false,
            ],
            [
                'name' => 'Lembur 2.5 jam',
                'type' => 'income',
                'amount' => 196128,
                'description' => '1.5 + 1.5×2',
                'is_default' => false,
            ],
            [
                'name' => 'Lembur 2.75 jam',
                'type' => 'income',
                'amount' => 217920,
                'description' => null,
                'is_default' => false,
            ],
            [
                'name' => 'Lembur 3 jam',
                'type' => 'income',
                'amount' => 239712,
                'description' => '1.5 + 2×2',
                'is_default' => false,
            ],
            [
                'name' => 'Lembur 3.5 jam',
                'type' => 'income',
                'amount' => 283296,
                'description' => '1.5 + 2.5×2',
                'is_default' => false,
            ],
            [
                'name' => 'Lembur 4 jam',
                'type' => 'income',
                'amount' => 326880,
                'description' => '1.5 + 3×2',
                'is_default' => false,
            ],
            [
                'name' => 'Lembur 2.15 jam',
                'type' => 'income',
                'amount' => 174336,
                'description' => null,
                'is_default' => false,
            ],
            [
                'name' => 'Lembur 3.75 jam',
                'type' => 'income',
                'amount' => 305088,
                'description' => null,
                'is_default' => false,
            ],
            [
                'name' => 'Lembur 4.15 jam',
                'type' => 'income',
                'amount' => 339955,
                'description' => null,
                'is_default' => false,
            ],
            [
                'name' => 'Lembur 1.75 jam',
                'type' => 'income',
                'amount' => 130752,
                'description' => null,
                'is_default' => false,
            ],
            [
                'name' => 'Lembur 9.75 jam weekend',
                'type' => 'income',
                'amount' => 958848,
                'description' => null,
                'is_default' => false,
            ],
        ];

        $userId = auth()->id() ?? User::query()->min('id');

        foreach ($categories as $index => $category) {
            Category::withoutGlobalScopes()->updateOrCreate(
                [
                    'user_id' => $userId,
                    'name' => $category['name'],
                    'type' => $category['type'],
                ],
                [
                    'amount' => $category['amount'],
                    'description' => $category['description'],
                    'is_default' => $category['is_default'] ?? false,
                    'order_no' => $category['order_no'] ?? ($index + 1),
                ]
            );
        }
    }
}
