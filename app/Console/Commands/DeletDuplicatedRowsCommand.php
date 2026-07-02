<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeletDuplicatedRowsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:deleteDuplicated';

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
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      // find the duplicate ids first.
      $duplicateIds = \DB::table("matchs")
        ->selectRaw("min(id) as id")
        ->groupBy("event_id")
        ->havingRaw('count(id) > ?', [1])
        ->pluck("id");

      if ($duplicateIds->isEmpty()) {
          $this->info("Nenhuma duplicata encontrada.");
          return;
      }

      // Delete duplicates, keeping the one with min id
      $deleted = \DB::table("matchs")
        ->whereNotIn("id", $duplicateIds)
        ->whereIn("event_id", function($query) {
            $query->selectRaw("event_id")
                ->from("matchs")
                ->groupBy("event_id")
                ->havingRaw("count(id) > ?", [1]);
        })
        ->delete();

      $this->info("Duplicatas removidas: {$deleted}");
    }
}
