<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Game;

class DownloadFlags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flags:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Baixa as bandeiras dos times da CDN antiga e salva localmente';

    // A URL original de onde vamos roubar as imagens
    private $baseUrl = 'https://d2x9mcd4yw5kj3.cloudfront.net/flags/soccer/b/';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando o sugador de bandeiras...');

        // Pegar todos os IDs de imagens que não são nulos nem 0
        $this->info('Buscando IDs no banco de dados...');
        
        $homeIds = Game::whereNotNull('image_id_home')->where('image_id_home', '!=', '0')->pluck('image_id_home')->toArray();
        $awayIds = Game::whereNotNull('image_id_away')->where('image_id_away', '!=', '0')->pluck('image_id_away')->toArray();

        // Juntar todos os IDs e tirar os repetidos
        $todosIds = array_unique(array_merge($homeIds, $awayIds));
        
        if (count($todosIds) === 0) {
            $this->warn('Nenhum ID de imagem encontrado nos jogos cadastrados atualmente.');
            return 0;
        }

        $this->info('Encontrados ' . count($todosIds) . ' times unicos. Iniciando o download...');

        // Garantir que a pasta public/flags/soccer/b/ exista
        $destinationPath = public_path('flags/soccer/b');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $baixados = 0;
        $erros = 0;
        $ignorados = 0;

        $bar = $this->output->createProgressBar(count($todosIds));
        $bar->start();

        foreach ($todosIds as $id) {
            $filename = $id . '.png';
            $localFile = $destinationPath . '/' . $filename;

            // Se a bandeira já existe na nossa pasta, a gente pula
            if (file_exists($localFile)) {
                $ignorados++;
            } else {
                try {
                    // Puxa da amazon
                    $response = Http::get($this->baseUrl . $filename);
                    
                    if ($response->successful()) {
                        file_put_contents($localFile, $response->body());
                        $baixados++;
                    } else {
                        $erros++;
                    }
                } catch (\Exception $e) {
                    $erros++;
                }
            }
            
            $bar->advance();
        }

        $bar->finish();
        
        $this->newLine();
        $this->info("=== Relatorio ===");
        $this->info("Bandeiras novas baixadas: {$baixados}");
        $this->info("Bandeiras ja existentes (ignoradas): {$ignorados}");
        $this->error("Erros ou nao encontradas na CDN: {$erros}");
        $this->info("=================");

        return 0;
    }
}
