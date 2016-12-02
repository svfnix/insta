<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Queue;
use GuzzleHttp\Exception\RequestException;
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
                $this->warn('please wait '.($time - time()).' seconds for unlocking.');
                return false;
            }
        }

        $instagram = new Instagram();
        $nodes = $instagram->getUserUpdates();

        $count = 0;
        $limit = 2;
        $counter = 0;
        try{
            foreach($nodes as $node){
                if($node->likes->viewer_has_liked == false){
                    $this->info((++$counter) . ') Update '. $node->id .' liked');
                    $instagram->like($node->id)->getBody();
		    sleep(30);

                    $count++;
                    if($count >= $limit){
                        return;
                    }
                } else {
                    $this->warn((++$counter) . ') Update '. $node->id .' liked previously');
                }
            }
        } catch (RequestException $e) {
            if ($e->getResponse()->getStatusCode() == '400') {
                //file_put_contents('like.lock', time() + 120);
                $this->error('your account has been locked ...');
                return false;
            }
        }
    }
}
