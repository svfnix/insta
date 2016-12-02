<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Queue;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class send_request_live extends Command
{
    protected $signature = 'insta:send_request_live';
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

        $limit = 4;
        $count = 0;
        $counter = 0;

        $this->info('Start following likers');

        $instagram = new Instagram();
        $nodes = $instagram->getUserUpdates();
        try {
            foreach ($nodes as $node) {
                if ($node->likes->count) {
                    foreach ($node->likes->nodes as $like) {
                        if ($like->user->id != $instagram->getUserID()) {
                            $user = DB::table('queues')->where('id', $like->user->id)->first();
                            if ($user) {
                                if (is_null($user->followed_at)) {
                                    $this->info((++$counter) . ') Follow liker/user : ' . $user->id . ' [' . $user->username . ']');
                                    DB::table('queues')->where('id', $user->id)->update(['followed_at' => new \DateTime()]);
                                    $instagram->follow($user->id);
				                    sleep(30);
                                    $count++;
                                }
                            } else {
                                $this->info((++$counter) . ') Follow liker : ' . $like->user->id . ' [' . $like->user->username . ']');

                                $queue = new Queue();
                                $queue->id = $like->user->id;
                                $queue->username = $like->user->username;
                                $queue->created_at = new \DateTime();
                                $queue->followed_at = new \DateTime();
                                $queue->cycle = -1;
                                $queue->save();

                                $instagram->follow($like->user->id);
                                sleep(30);
                                $count++;
                            }

                            if ($count >= $limit) {
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

        $this->info('Start following users');

        $queues = DB::table('queues')->whereNull('followed_at')->limit( $limit - $count )->get();
        foreach($queues as $queue){
            $this->info((++$counter) . ') Follow user : ' . $queue->id .' ['. $queue->username .']');
            DB::table('queues')->where('id',  $queue->id)->update(['followed_at' => new \DateTime()]);
            $instagram->follow($queue->id);
        }
    }
}
