<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Queue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Mockery\CountValidator\Exception;

class like extends Command
{

    protected $signature = 'insta:like';
    protected $description = 'Like updates';

    public function handle()
    {

        if(file_exists('like.lock')){
            $time = file_get_contents('like.lock');
            if($time < time()){
                unlink('like.lock');
            } else {
                return false;
            }
        }

        $instagram = new Instagram();
        $nodes = $instagram->getUserUpdates();

        $count = 0;
        $limit = 9;
        $counter = 0;
        foreach($nodes as $node){
            if($node->likes->viewer_has_liked == false){
                $this->info((++$counter) . ') Update '. $node->id .' liked');
                
                try{
                    $instagram->like($node->id)->getBody();
                } catch (RequestException $e) {
                    if ($e->getResponse()->getStatusCode() == '400') {
                        file_put_contents('like.lock', time() + 1800);
                        return false;
                    }
                }

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
