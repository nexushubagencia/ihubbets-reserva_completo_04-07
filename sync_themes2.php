<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$themes = App\Models\GlobalTheme::all();
foreach($themes as $theme) {
    $selectedTheme = is_array($theme->colors) ? $theme->colors : json_decode($theme->colors, true);
    
    $c = function($field, $default) use ($selectedTheme) {
        return $selectedTheme[$field] ?? $default;
    };
    
    // Core fallback values matching SettingsController
    $primary    = $c('primary_color', '#285e3d');
    $sidebar    = $c('sidebar_color', '#031808');
    $oddsPlus   = $c('odds_plus_button_color', '#1aa6d0');

    // Fill in the blanks
    $selectedTheme['cupom_valor_btn_color'] = $c('cupom_valor_btn_color', $oddsPlus);
    $selectedTheme['cupom_header_color'] = $c('cupom_header_color', $sidebar);
    $selectedTheme['cupom_apostar_btn_color'] = $c('cupom_apostar_btn_color', $primary);
    $selectedTheme['odds_plus_button_color'] = $oddsPlus;
    
    // Save it back
    $theme->colors = $selectedTheme;
    $theme->save();
}

echo "Themes synchronized.\n";
