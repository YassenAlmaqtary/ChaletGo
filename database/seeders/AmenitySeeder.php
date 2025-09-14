<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Amenity;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            // General amenities
            ['name' => 'واي فاي مجاني', 'icon' => 'fas fa-wifi', 'category' => 'general'],
            ['name' => 'مكيف هواء', 'icon' => 'fas fa-snowflake', 'category' => 'comfort'],
            ['name' => 'تلفزيون', 'icon' => 'fas fa-tv', 'category' => 'entertainment'],
            ['name' => 'مطبخ مجهز', 'icon' => 'fas fa-utensils', 'category' => 'general'],
            ['name' => 'غسالة ملابس', 'icon' => 'fas fa-tshirt', 'category' => 'general'],

            // Outdoor amenities
            ['name' => 'مسبح خاص', 'icon' => 'fas fa-swimming-pool', 'category' => 'outdoor'],
            ['name' => 'حديقة', 'icon' => 'fas fa-tree', 'category' => 'outdoor'],
            ['name' => 'شواء', 'icon' => 'fas fa-fire', 'category' => 'outdoor'],
            ['name' => 'موقف سيارات', 'icon' => 'fas fa-car', 'category' => 'general'],
            ['name' => 'جلسة خارجية', 'icon' => 'fas fa-chair', 'category' => 'outdoor'],

            // Entertainment
            ['name' => 'ألعاب فيديو', 'icon' => 'fas fa-gamepad', 'category' => 'entertainment'],
            ['name' => 'طاولة بلياردو', 'icon' => 'fas fa-circle', 'category' => 'entertainment'],
            ['name' => 'طاولة تنس', 'icon' => 'fas fa-table-tennis', 'category' => 'entertainment'],

            // Safety
            ['name' => 'كاميرات مراقبة', 'icon' => 'fas fa-video', 'category' => 'safety'],
            ['name' => 'حارس أمن', 'icon' => 'fas fa-shield-alt', 'category' => 'safety'],
            ['name' => 'إنذار حريق', 'icon' => 'fas fa-fire-extinguisher', 'category' => 'safety'],

            // Comfort
            ['name' => 'مدفأة', 'icon' => 'fas fa-fire', 'category' => 'comfort'],
            ['name' => 'جاكوزي', 'icon' => 'fas fa-hot-tub', 'category' => 'comfort'],
            ['name' => 'ساونا', 'icon' => 'fas fa-thermometer-half', 'category' => 'comfort'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::create($amenity);
        }
    }
}
