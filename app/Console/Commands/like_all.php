<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Queue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class like_all extends Command
{

    protected $signature = 'insta:like_all';
    protected $description = 'Like updates';

    public function handle()
    {
        $instagram = new Instagram();
        $id = $instagram->getUserID($this->argument('user'));




        $nodes = $instagram->getUpdates();

        $counter = 0;
        foreach($nodes as $node){
            if($node->likes->viewer_has_liked == false){
                $instagram->like($node->id);
                $this->info((++$counter) . ') Update '. $node->id .' liked');
            } else {
                $this->error((++$counter) . ') Update '. $node->id .' liked previously');
            }
        }
    }
}
