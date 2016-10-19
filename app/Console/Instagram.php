<?php
namespace App\Console;

class Instagram {

    private $client;
    private $jar;

    public $userid;
    public $username;
    private $password;

    public $page_count = 100;

    public function __construct()
    {
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

    public function getUserID($uname){

        $response = $this->client->request('GET', 'https://www.instagram.com/'.$uname.'/', [
            'cookies' => $this->jar
        ]);
        preg_match('/\"id\"\: "([0-9]+)\"/Si', $response->getBody(), $matchs);

        return $matchs[1];
    }

    public function query($path, $args=[]){
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

    public function follow($id){
        return $this->query('https://www.instagram.com/web/friendships/'. $id .'/follow/');
    }

    public function unfollow($id){
        return $this->query('https://www.instagram.com/web/friendships/'. $id .'/unfollow/');
    }

    public function getFollowers($userid, $after=null){

        if($after){
            $page_tpl = 'after('. $after .',+'. $this->page_count .')';
        } else {
            $page_tpl = 'first('. $this->page_count .')';
        }

        return $this->query(
            'https://www.instagram.com/query/', [
            'q' => 'ig_user('. $userid .'){followed_by.'. $page_tpl .'{count,page_info{end_cursor,has_next_page},nodes{id,is_verified,followed_by_viewer,requested_by_viewer,full_name,profile_pic_url,username}}}',
        ])->getBody();
    }

    public function getFollows($userid, $after=null){

        if($after){
            $page_tpl = 'after('. $after .',+'. $this->page_count .')';
        } else {
            $page_tpl = 'first('. $this->page_count .')';
        }

        return $this->query(
            'https://www.instagram.com/query/', [
            'q' => 'ig_user('. $userid .'){follows.'. $page_tpl .'{count,page_info{end_cursor,has_next_page},nodes{id,is_verified,followed_by_viewer,requested_by_viewer,full_name,profile_pic_url,username}}}',
        ])->getBody();
    }

}
