<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Queue;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class send_request extends Command
{
    protected $signature = 'insta:send-request';
    protected $description = 'Send follow request live data';

    public function handle()
    {

        if(file_exists('follow.lock')){
            $time = file_get_contents('follow.lock');
            if($time < time()){
                unlink('follow.lock');
            } else {
                $this->warn('please wait '.($time - time()).' seconds for unlocking.');
                return false;
            }
        }

        $count = 0;

        $this->info('Start following likers');

        $instagram = new Instagram();
        $nodes = $instagram->getUserUpdates();
        try {
            foreach ($nodes as $node) {
                if ($node->node->edge_media_preview_like->count) {
                    foreach ($node->node->edge_media_preview_like->edges as $like) {
                        if ($like->node->id != $instagram->getUserID()) {
                            $user = DB::table('queues')->where('id', $like->node->id)->first();
                            if (!$user) {
                                $this->info($count . ') Follow liker : ' . $like->node->id . ' [' . $like->node->username . ']');

                                $queue = new Queue();
                                $queue->id = $like->node->id;
                                $queue->username = $like->node->username;
                                $queue->created_at = new \DateTime();
                                $queue->followed_at = new \DateTime();
                                $queue->cycle = -1;
                                $queue->save();

                                $instagram->follow($like->node->id);
                                $count++;
                            }

                            if ($count >= 5) {
                                return;
                            }
                        }
                    }
                }
            }
        } catch (RequestException $e) {
            if ($e->getResponse()->getStatusCode() == '400') {
                //file_put_contents('follow.lock', time() + 60);
                $this->error('your account has been locked ...');
                return false;
            }
        }
    }
}
