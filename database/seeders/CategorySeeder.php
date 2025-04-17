<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CategorySeeder extends Seeder
{
    public function run()
    {
        // Insert categories into the categories table
        DB::table('categories')->insert([
            [
                'parent_id' => null,
                'name' => 'Local (Geo-targeted)',
                'description' => 'Geographically focused content or services.',
                'image' => 'local-geo-targeted.png', // Replace with actual image path or file name
            ],
            [
                'parent_id' => null,
                'name' => 'Family',
                'description' => 'Content or services aimed at family-friendly activities.',
                'image' => 'family.png', // Replace with actual image path or file name
            ],
            [
                'parent_id' => null,
                'name' => 'Cultural and Diverse',
                'description' => 'Content celebrating different cultures and diversity.',
                'image' => 'cultural-diverse.png', // Replace with actual image path or file name
            ],
            [
                'parent_id' => null,
                'name' => 'Sustainable',
                'description' => 'Content or services promoting sustainability and environmental awareness.',
                'image' => 'sustainable.png', // Replace with actual image path or file name
            ],
            [
                'parent_id' => null,
                'name' => 'Nonprofit',
                'description' => 'Content or services supporting nonprofit causes and organizations.',
                'image' => 'nonprofit.png', // Replace with actual image path or file name
            ],
            [
                'parent_id' => null,
                'name' => 'Other',
                'description' => 'Content or services that don\'t fit into the predefined categories.',
                'image' => 'other.png', // Replace with actual image path or file name
            ],
        ]);
    }
}