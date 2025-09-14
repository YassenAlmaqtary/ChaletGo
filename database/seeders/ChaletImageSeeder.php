<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Chalet;
use App\Models\ChaletImage;
use Illuminate\Support\Facades\Storage;

class ChaletImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chalets = Chalet::all();

        // Sample image URLs (you can replace with actual images)
        $sampleImages = [
            'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800',
            'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800',
            'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800',
            'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800',
            'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800',
        ];

        foreach ($chalets as $chalet) {
            // Create 3-5 images per chalet
            $imageCount = rand(3, 5);

            for ($i = 0; $i < $imageCount; $i++) {
                ChaletImage::create([
                    'chalet_id' => $chalet->id,
                    'image_path' => 'chalets/sample_' . $chalet->id . '_' . ($i + 1) . '.jpg',
                    'alt_text' => $chalet->name . ' - صورة ' . ($i + 1),
                    'is_primary' => $i === 0, // First image is primary
                    'sort_order' => $i + 1,
                ]);
            }
        }
    }
}
