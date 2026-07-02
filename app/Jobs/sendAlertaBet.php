<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Bet;
use Illuminate\Support\Facades\Mail;

class sendAlertaBet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $aposta;
    protected $email;

    public function __construct(Bet $aposta, $email)
    {
        $this->aposta = $aposta;
        $this->email  = $email;
    }

    public function handle()
    {
        $bet = $this->aposta;

        $subject = "Alerta de Aposta #{$bet->id} - {$bet->codigo_bilhete}";

        $body = "Uma nova aposta foi realizada no sistema.\n\n";
        $body .= "Código: {$bet->codigo_bilhete}\n";
        $body .= "Valor: R$ " . number_format($bet->valor_total, 2, ',', '.') . "\n";
        $body .= "Cotação: {$bet->cotacao_total}\n";
        $body .= "Prêmio Potencial: R$ " . number_format($bet->premio_total, 2, ',', '.') . "\n";
        $body .= "Status: {$bet->status}\n";
        $body .= "Data: {$bet->created_at}\n";

        Mail::raw($body, function ($message) use ($subject) {
            $message->to($this->email)
                    ->subject($subject);
        });
    }
}
