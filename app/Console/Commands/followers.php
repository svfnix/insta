<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

function __die($msg){
    die("{$msg}\n");
}

class followers extends Command
{

    protected $signature = 'insta:run';
    protected $description = 'update instagram followers';

    private $client;
    private $jar;

    private $username;
    private $password;
    private $userid;

    public function __construct()
    {
        parent::__construct();
        $this->client = new \GuzzleHttp\Client();
        $this->jar = new \GuzzleHttp\Cookie\CookieJar;

        $this->username = 'svfnix';
        $this->password = base64_decode('YHMsdm5saw==');
        $this->userid = '485981610';
    }

    public function getCsrfToken(){

        $response = $this->client->request('GET', 'https://www.instagram.com/', [
            'cookies' => $this->jar
        ]);
        preg_match('/\"csrf_token\"\: "([a-zA-Z0-9\-\_]+)\"/Si', $response->getBody(), $matchs);

        return $matchs[1];
    }

    public function query($path, $args){
        return $this->client->request('POST', $path, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:49.0) Gecko/20100101 Firefox/49.0',
                'X-CSRFToken' => $this->getCsrfToken(),
                'X-Instagram-AJAX' => '1',
                'X-Requested-With' => 'XMLHttpRequest',
                'Referer' => 'https://www.instagram.com/'
            ],
            'cookies' => $this->jar,
            'form_params' => $args
        ]);
    }

    public function login(){
        return $this->query(
            'https://www.instagram.com/accounts/login/ajax/', [
                'username' => $this->username,
                'password' => $this->password,
            ])->getBody();
    }

    public function getFollowers($userid){
        return $this->query(
            'https://www.instagram.com/query/', [
            'q' => 'ig_user('. $userid .'){followed_by.first(10){count,page_info{end_cursor,has_next_page},nodes{id,is_verified,followed_by_viewer,requested_by_viewer,full_name,profile_pic_url,username}}}',
        ])->getBody();
    }

    public function getFollowing($userid){
        return $this->query(
            'https://www.instagram.com/query/', [
            'q' => 'ig_user('. $userid .'){follows.first(10){count,page_info{end_cursor,has_next_page},nodes{id,is_verified,followed_by_viewer,requested_by_viewer,full_name,profile_pic_url,username}}}',
        ])->getBody();
    }


    public function handle()
    {
        $response = json_decode($this->login());
        if(!$response || !$response->authenticated){
            __die('Login Failed!');
        }

        $response = json_decode($this->getFollowers($this->userid));
        file_put_contents('result', print_r($response, 1));
        print_r($response);
    }
}
