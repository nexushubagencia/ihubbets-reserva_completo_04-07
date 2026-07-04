<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Bet;

class EmailAlertaAposta extends Mailable
{
    use Queueable, SerializesModels;

    private $bet;

    public function __construct(Bet $bet)
    {
        $this->bet = $bet;
    }

    public function build()
    {
        return $this->markdown('emails.alertAposta')
                        ->subject('Alerta de Aposta #'.$this->bet->id)
                        ->with([
                            'bet' => $this->bet,
                        ]);
    }
}
