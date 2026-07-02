<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use Carbon\Carbon;

class InsertImagem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    private $token;
    private $agora;
    private $today;
    private $tomorrow;
    private $hoje;
    private $amanha;

    protected $signature = 'command:insertImagen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->agora    = $agora = Carbon::now()->format('Y-m-d H:i:s');
        $this->today    = $today = Carbon::today()->format('Y-m-d');
        $this->tomorrow = $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $this->hoje     = $hoje = Carbon::today()->format('Ymd');
        $this->amanha   = $amanha = Carbon::tomorrow()->format('Ymd');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        

        $ci= 1;
             
        $c = 0;
        $result = array();
        $url = 'https://api.b365api.com/v1/betfair/ex/upcoming?sport_id=1&LNG_ID=22&token='.env('TOKEN');
        //$url = 'https://api.b365api.com/v1/bet365/upcoming?sport_id=1&LNG_ID=22&token=7234-v61j1DRloLNq5r&day=20181129';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $matchs = curl_exec($ch);
        if ($matchs === false) {
            $info = curl_getinfo($ch);
            curl_close($ch);

            die('error occured during curl exec. Additioanl info: ' . var_export($info));

        }
        curl_close($ch);


        $matchs = json_decode($matchs);

        

        //$this->arr[] = $matchs->results;

        $pagina = intval($matchs->pager->total/$matchs->pager->per_page);
        //dd($pagina);
        $cont = 1;
        $j=0;
        for($i=0; $i<= ($pagina); $i++) {
            $url2 = 'https://api.b365api.com/v1/betfair/ex/upcoming?sport_id=1&LNG_ID=22&token='.env('TOKEN').'&page='.$i;
            //dd($url2);
            $ch2 = curl_init($url2);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            $matchs2 = curl_exec($ch2);
            $m = json_decode($matchs2);

            if ($matchs2 === false) {
                $info2 = curl_getinfo($ch2);
                curl_close($ch2);


                die('error occured during curl exec. Additioanl info: ' . var_export($info2));


            }
            curl_close($ch2);

          
              
            
            foreach($m->results as $match) {



                            $url = 'https://api.b365api.com/v1/betfair/ex/result?token='.env('TOKEN').'&LNG_ID=22&event_id='.$match->id;
                            //$url = 'https://api.betsapi.com/v1/bet365/result?token=21368-2r659Nn1mN2auq&event_id=79733062';
                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $matchs_update = curl_exec($ch);
                            if ($matchs_update === false) {
                                $info = curl_getinfo($ch);
                                curl_close($ch);

                                die('error occured during curl exec. Additioanl info: ' . var_export($info));

                            }
                            curl_close($ch);

                            $matchs_update = json_decode($matchs_update);
                    
                        
                        
                            foreach($matchs_update->results as $result) {

                        
                                    echo "Inserindo..\n";
                                    if($result->home->image_id != null) {

                                        $url_img_home = "https://assets.b365api.com/images/team/m/" . $result->home->image_id. ".png";

                                        $EndImagem = "https://assets.b365api.com/images/team/m/299996.png"; //Endereço da imagem. Pode ser também uma URL

                                        //Pegando as informações da imagem
                                        $TamanhoImagem = getimagesize($url_img_home);
                                        $Estensao = substr($url_img_home,-3);
                                        
                                        if($TamanhoImagem[0] == 50) {
                                            $ch_img_home = curl_init($url_img_home);
                                        
                                            $fp_home = fopen('public/images/teams/m/'.$result->home->image_id.'.png', 'wb');
                                        
                                            curl_setopt($ch_img_home, CURLOPT_FILE, $fp_home);
                                            curl_setopt($ch_img_home, CURLOPT_HEADER, 0);
                                            curl_exec($ch_img_home);
                                            curl_close($ch_img_home);
                                            fclose($fp_home);
    
                                        }
                                           
                                       

                                    }
                                

                                    if($result->away->image_id != null) {

                                        $url_img_away = "https://assets.b365api.com/images/team/m/" . $result->away->image_id. ".png";
                            
                                        //Pegando as informações da imagem
                                        $TamanhoImagem = getimagesize($url_img_home);
                                        $Estensao = substr($url_img_home,-3);
                                        
                                        if($TamanhoImagem[0] == 50) {
                                            $ch_img_away = curl_init($url_img_away);
                                        
                                            $fp_away = fopen('public/images/teams/m/'.$result->away->image_id.'.png', 'wb');
                                        
                                            curl_setopt($ch_img_away, CURLOPT_FILE, $fp_away);
                                            curl_setopt($ch_img_away, CURLOPT_HEADER, 0);
                                            curl_exec($ch_img_away);
                                            curl_close($ch_img_away);
                                            fclose($fp_away);
                                        }   
                                        

                                    }

                                
                                
                            }
                    

                }
             }

    
        
    }
}
