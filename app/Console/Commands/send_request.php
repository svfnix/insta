<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class send_request extends Command
{
    protected $signature = 'insta:send_request';
    protected $description = 'Send follow request';

    public function handle()
    {
        $this->info('Start following users');

        $instagram = new Instagram();
        $response = json_decode($instagram->login());
        if(!$response || !$response->authenticated){
            $this->error('Login Failed!');
        }

        $queues = DB::table('queues')->whereNull('followed_at')->limit(2)->get();
        foreach($queues as $queue){
            $this->info('Follow user : ' . $queue->id .' ['. $queue->username .']');
            DB::table('queues')->where('id',  $queue->id)->update(['followed_at' => new \DateTime()]);
            $instagram->follow($queue->id);
        }
    }
}
