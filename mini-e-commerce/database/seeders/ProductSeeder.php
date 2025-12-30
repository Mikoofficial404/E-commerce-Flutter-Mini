<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'product_name' => 'Nike Air Max 270',
                'description' => 'Sepatu running dengan teknologi Air Max untuk kenyamanan maksimal. Cocok untuk aktivitas sehari-hari dan olahraga ringan.',
                'price' => 2499000,
                'stock' => 50,
                'photo_product' => 'products/nike-air-max-270.jpg',
            ],
            [
                'product_name' => 'Adidas Ultraboost 22',
                'description' => 'Sepatu lari premium dengan teknologi Boost untuk responsivitas dan energi return terbaik.',
                'price' => 3200000,
                'stock' => 35,
                'photo_product' => 'products/adidas-ultraboost-22.jpg',
            ],
            [
                'product_name' => 'Puma RS-X',
                'description' => 'Sepatu sneakers retro dengan desain chunky yang stylish. Perfect untuk street style.',
                'price' => 1899000,
                'stock' => 40,
                'photo_product' => 'products/puma-rs-x.jpg',
            ],
            [
                'product_name' => 'New Balance 574',
                'description' => 'Sepatu klasik dengan kenyamanan legendaris. Cocok untuk casual dan daily wear.',
                'price' => 1599000,
                'stock' => 60,
                'photo_product' => 'products/new-balance-574.jpg',
            ],
            [
                'product_name' => 'Converse Chuck Taylor All Star',
                'description' => 'Sepatu canvas iconic yang timeless. Cocok untuk segala gaya dan kesempatan.',
                'price' => 899000,
                'stock' => 100,
                'photo_product' => 'products/converse-chuck-taylor.jpg',
            ],
            [
                'product_name' => 'Vans Old Skool',
                'description' => 'Sepatu skateboard klasik dengan side stripe yang ikonik. Favorit para skater dan fashion enthusiast.',
                'price' => 999000,
                'stock' => 75,
                'photo_product' => 'products/vans-old-skool.jpg',
            ],
            [
                'product_name' => 'Reebok Classic Leather',
                'description' => 'Sepatu kulit klasik dengan desain minimalis. Nyaman untuk penggunaan sehari-hari.',
                'price' => 1299000,
                'stock' => 45,
                'photo_product' => 'products/reebok-classic-leather.jpg',
            ],
            [
                'product_name' => 'Asics Gel-Kayano 29',
                'description' => 'Sepatu lari stability dengan teknologi GEL untuk penyerapan shock maksimal.',
                'price' => 2799000,
                'stock' => 30,
                'photo_product' => 'products/asics-gel-kayano-29.jpg',
            ],
            [
                'product_name' => 'Nike Air Force 1 Low',
                'description' => 'Sepatu basketball legendaris yang menjadi ikon streetwear. Desain clean dan versatile.',
                'price' => 1749000,
                'stock' => 80,
                'photo_product' => 'products/nike-air-force-1.jpg',
            ],
            [
                'product_name' => 'Adidas Stan Smith',
                'description' => 'Sepatu tennis klasik dengan desain minimalis. Cocok untuk tampilan casual yang elegan.',
                'price' => 1499000,
                'stock' => 55,
                'photo_product' => 'products/adidas-stan-smith.jpg',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
