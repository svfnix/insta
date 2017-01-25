<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Queue;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class unfollow extends Command
{
    protected $signature = 'insta:unfollow';
    protected $description = 'unfollow';

    public function handle()
    {
        $counter = 0;
        $instagram = new Instagram();

        $friends = DB::table('queues')
            ->orderBy('unfollowed_at', 'asc')
            ->orderBy('followed_at', 'asc')
            ->take(10)
            ->get();

        $this->info('Start unfollowing friends');
        foreach ($friends as $friend){
            try {
                DB::table('queues')->where('id', $friend->id)->update(['unfollowed_at' => new \DateTime()]);
                $result = $instagram->unfollow($friend->id);
                $this->info($result->getBody());
                $this->info((++$counter) . ') Unfollow user : ' . $friend->id . ' [' . $friend->username . ']');
            } catch (RequestException $e) {
                $this->info($e->getMessage());
            }
        }

    }
}
