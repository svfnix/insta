<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Queue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class like_all extends Command
{

    protected $signature = 'insta:like_all {user}';
    protected $description = 'Like user updates';

    public function handle()
    {
        $instagram = new Instagram();

        $nodes = json_decode(
            $instagram->getUpdates(
                $instagram->getUserID(
                    $this->argument('user')
                )
        ));

        $counter = 0;
        foreach ($nodes->media->nodes as $node){
            $instagram->like($node->id);
            $this->info((++$counter) . ') Like post '. $node->id);
        }
    }
}
