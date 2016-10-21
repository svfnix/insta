<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Follower;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class accept_request extends Command
{
    protected $signature = 'insta:accept_request';
    protected $description = 'Accept follow request';

    public function handle()
    {
        $this->info('Get list of requests');

        $instagram = new Instagram();
        $content = $instagram->query($instagram->route('/accounts/activity/?__a=1'), [], 'GET')->getBody();
        $content = json_decode($content);

        if(count($content->followRequests)) {
            $this->info('You have ' . count($content->followRequests) . ' follow requests');
            $counter = 0;
            foreach ($content->followRequests as $request) {
                $this->info((++$counter) . ') Accept Request : ' . $request->id . ' [' . $request->username . ': ' . $request->full_name . ']');
                $instagram->accept($request->id);

                $follower = new Follower();
                $follower->id = $request->id;
                $follower->username = $request->username;
                $follower->created_at = new \DateTime();
                $follower->save();
            }
        }
    }
}
