<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Queue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class fetch extends Command
{

    protected $signature = 'insta:fetch {user}';
    protected $description = 'Fetch instagram user';

    public function handle()
    {
        $instagram = new Instagram();
        $response = json_decode($instagram->login());
        if(!$response || !$response->authenticated){
            $this->error('Login Failed!');
        }

        $this->info('fetch user');
        $id = $instagram->getUserID($this->argument('user'));

        if($id){
            if(DB::table('queues')->find($id)) {
                $this->error('User already exists.');
            } else {
                $queue = new Queue();
                $queue->id = $id;
                $queue->username = $this->argument('user');
                $queue->created_at = new \DateTime();
                $queue->save();
                $this->info('User fetched successfully.');
            }
        }
    }
}
