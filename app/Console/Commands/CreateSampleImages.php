<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use App\Models\Chalet;

class CreateSampleImages extends Command
{
    protected $signature = 'chalets:create-sample-images';
    protected $description = 'Create sample images for chalets';

    public function handle()
    {
        $manager = new ImageManager(new Driver());
        $chalets = Chalet::all();

        $colors = [
            [135, 206, 235], // Sky blue
            [144, 238, 144], // Light green
            [255, 182, 193], // Light pink
            [255, 218, 185], // Peach
            [221, 160, 221], // Plum
        ];

        foreach ($chalets as $chalet) {
            $this->info("Creating images for chalet: {$chalet->name}");
            
            for ($i = 1; $i <= 3; $i++) {
                $color = $colors[($chalet->id + $i) % count($colors)];
                
                // Create a simple colored image
                $image = $manager->create(800, 600)->fill("rgb({$color[0]}, {$color[1]}, {$color[2]})");

                $filename = "sample_{$chalet->id}_{$i}.jpg";
                $path = "chalets/{$filename}";
                
                Storage::disk('public')->put($path, $image->toJpeg(80));
                
                $this->info("Created: {$path}");
            }
        }

        $this->info('Sample images created successfully!');
    }
}
