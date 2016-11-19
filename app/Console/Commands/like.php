<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Queue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class like extends Command
{

    protected $signature = 'insta:like';
    protected $description = 'Like updates';

    public function handle()
    {
        $instagram = new Instagram();
        $nodes = $instagram->getUserUpdates();

        $count = 0;
        $limit = 9;
        $counter = 0;
        foreach($nodes as $node){
            if($node->likes->viewer_has_liked == false){
                $this->info((++$counter) . ') Update '. $node->id .' liked');
                $instagram->like($node->id);
                $count++;

                if($count >= $limit){
                    return;
                }
            } else {
                $this->warn((++$counter) . ') Update '. $node->id .' liked previously');
            }
        }
    }
}
