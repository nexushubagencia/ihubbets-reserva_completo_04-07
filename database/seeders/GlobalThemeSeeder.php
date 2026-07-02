<?php

namespace Database\Seeders;

use App\Models\GlobalTheme;
use Illuminate\Database\Seeder;

class GlobalThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = [
            [
                'name' => 'Verde IHUB Master',
                'slug' => 'verde-claro',
                'colors' => [
                    'primary_color' => '#008f45',
                    'secondary_color' => '#02a351',
                    'sidebar_color' => '#004d25',
                    'background_color' => '#012111',
                    'header_color' => '#000000',
                    'footer_bg_color' => '#002e16',
                    'cupom_header_color' => '#008f45',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Deep Blue Onyx',
                'slug' => 'azul-escuro',
                'colors' => [
                    'primary_color' => '#0d47a1',
                    'secondary_color' => '#1565c0',
                    'sidebar_color' => '#0a192f',
                    'background_color' => '#020b1a',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Red Phoenix',
                'slug' => 'vermelho',
                'colors' => [
                    'primary_color' => '#d32f2f',
                    'secondary_color' => '#f44336',
                    'sidebar_color' => '#1a1a1a',
                    'background_color' => '#0a0a0a',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Cyber Purple',
                'slug' => 'roxo',
                'colors' => [
                    'primary_color' => '#8e24aa',
                    'secondary_color' => '#7b1fa2',
                    'sidebar_color' => '#1e0b36',
                    'background_color' => '#0d041a',
                ],
                'is_active' => true,
            ]
        ];

        foreach ($themes as $theme) {
            GlobalTheme::updateOrCreate(['slug' => $theme['slug']], $theme);
        }
    }
}
