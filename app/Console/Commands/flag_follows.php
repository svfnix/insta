<?php

namespace App\Console\Commands;

use App\Queue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class flag_follows extends Command
{
    protected $signature = 'insta:flag_follows';
    protected $description = 'Update follows list in queue';

    public function handle()
    {

        $this->info('Reset queue followed_at field ...');
        DB::table('queues')->whereNotNull('followed_at')->update(['followed_at' => null]);

        $this->info('Update queue ...');
        $follows = DB::table('follows')->get();

        $counter = 0;
        foreach($follows as $follow){

            if(DB::table('queues')->where('id', $follow->id)->first()) {
                $this->info((++$counter) . ') Follow exists and will be updated : ' . $follow->id .' ['. $follow->username .']');
                DB::table('queues')->where('id', $follow->id)->update(['followed_at' => new \DateTime()]);
            } else {

                $this->warn((++$counter) . ') Follow not exists sand will be added : ' . $follow->id .' ['. $follow->username .']');
                
                $queue = new Queue();
                $queue->id = $follow->id;
                $queue->username = $follow->username;
                $queue->created_at = new \DateTime();
                $queue->followed_at = new \DateTime();
                $queue->cycle = 0;
                $queue->save();
                
            }
        }
    }
}
