<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaticPage\StaticPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StaticPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StaticPage::insert([
            [
                'slug' => 'terms',
                'title' => 'الشروط والأحكام',
                'content' => '<h1>الشروط والأحكام</h1><p>هذا نص الشروط...</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'privacy',
                'title' => 'سياسة الخصوصية',
                'content' => '<h1>سياسة الخصوصية</h1><p>هذا نص السياسة...</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'cookies',
                'title' => 'سياسة الكوكيز',
                'content' => '<h1>سياسة الكوكيز</h1><p>هذا نص سياسة الكوكيز...</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
