<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Chalet;
use App\Models\User;
use App\Models\Amenity;

class ChaletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::where('user_type', 'owner')->first();

        if (!$owner) {
            return;
        }

        $chalets = [
            [
                'name' => 'شاليه الواحة الذهبية',
                'description' => 'شاليه فاخر مع مسبح خاص وحديقة واسعة، يتسع لـ 8 أشخاص مع جميع وسائل الراحة الحديثة.',
                'location' => 'الرياض - حي الملقا',
                'latitude' => 24.7136,
                'longitude' => 46.6753,
                'price_per_night' => 500.00,
                'max_guests' => 8,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'is_featured' => true,
            ],
            [
                'name' => 'استراحة النخيل الأخضر',
                'description' => 'استراحة هادئة محاطة بالنخيل مع مرافق شواء وجلسات خارجية مريحة.',
                'location' => 'جدة - شمال جدة',
                'latitude' => 21.4858,
                'longitude' => 39.1925,
                'price_per_night' => 350.00,
                'max_guests' => 6,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'is_featured' => false,
            ],
            [
                'name' => 'شاليه البحر الأزرق',
                'description' => 'شاليه على البحر مباشرة مع إطلالة خلابة ومرافق مائية متنوعة.',
                'location' => 'الدمام - الكورنيش',
                'latitude' => 26.4207,
                'longitude' => 50.0888,
                'price_per_night' => 750.00,
                'max_guests' => 10,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'is_featured' => true,
            ],
        ];

        foreach ($chalets as $chaletData) {
            $chalet = Chalet::create([
                'owner_id' => $owner->id,
                'name' => $chaletData['name'],
                'description' => $chaletData['description'],
                'location' => $chaletData['location'],
                'latitude' => $chaletData['latitude'],
                'longitude' => $chaletData['longitude'],
                'price_per_night' => $chaletData['price_per_night'],
                'max_guests' => $chaletData['max_guests'],
                'bedrooms' => $chaletData['bedrooms'],
                'bathrooms' => $chaletData['bathrooms'],
                'is_active' => true,
                'is_featured' => $chaletData['is_featured'],
            ]);

            // Attach random amenities
            $amenities = Amenity::inRandomOrder()->limit(rand(5, 10))->pluck('id');
            $chalet->amenities()->attach($amenities);
        }
    }
}
