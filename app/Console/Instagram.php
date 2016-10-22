<?php
namespace App\Console;

use GuzzleHttp\Cookie\FileCookieJar;

class Instagram {

    public $data;
    private $client;
    private $jar;

    public $userid;
    public $username;
    private $password;

    public $page_count_follow = 100;
    public $page_count_stuff = 30;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
        //$this->jar = new \GuzzleHttp\Cookie\CookieJar();
        $this->jar = new FileCookieJar('storage/'.env('INSTA_USERNAME', '_').'.jar');

        $this->username = env('INSTA_USERNAME', null);
        $this->password = env('INSTA_PASSWORD', null);

        $this->fetchData();

    }

    public function route($path=null){
        return "https://www.instagram.com/" . ltrim($path, '/');
    }

    public function fetch($url){
        $response = $this->client->request('GET', $url, [
            'cookies' => $this->jar
        ]);

        preg_match('/\<script type=\"text\/javascript\"\>window\.\_sharedData \= (\{.*?\})\;\<\/script\>/Si', $response->getBody(), $matchs);
        return json_decode($matchs[1]);
    }

    public function fetchData(){
        $this->data = $this->fetch($this->route());
    }

    public function getCsrfToken(){
        return $this->data->config->csrf_token;
    }

    public function getUserID($uname=null){

        if(is_null($uname)){
            return $this->data->config->viewer->id;
        }

        $data = $this->fetch(
            $this->route("/{$uname}/")
        );

        return $data->entry_data->ProfilePage[0]->user->id;
    }

    public function getUserUpdates($uname=null){
        if(is_null($uname)){
            return $this->data->entry_data->FeedPage[0]->feed->media->nodes;
        }

        $data = $this->fetch($this->route($uname));
        return $data->entry_data->ProfilePage[0]->user->media->nodes;
    }

    public function query($path, $args=[], $method='POST'){

        if(!is_array($args)){
            $args = [];
        }

        if(count($args)){
            $args = ['form_params' => $args];
        }

        $args['cookies'] = $this->jar;
        $args['headers'] = [
            'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:49.0) Gecko/20100101 Firefox/49.0',
            'X-CSRFToken' => $this->getCsrfToken(),
            'X-Instagram-AJAX' => '1',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer' => 'https://www.instagram.com/'
        ];
        return $this->client->request($method, $path, $args);
    }

    public function login(){
        return $this->query(
            $this->route("/accounts/login/ajax/"), [
            'username' => $this->username,
            'password' => $this->password,
        ])->getBody();
    }

    public function logout(){
        return $this->query(
            $this->route("/accounts/logout/"), [
            'csrfmiddlewaretoken' => $this->getCsrfToken()
        ])->getBody();
    }

    public function follow($id){
        return $this->query($this->route("/web/friendships/{$id}/follow/"));
    }

    public function unfollow($id){
        return $this->query($this->route("/web/friendships/{$id}/unfollow/"));
    }

    public function like($id){
        return $this->query($this->route("/web/likes/{$id}/like/"));
    }

    public function unlike($id){
        return $this->query($this->route("/web/likes/{$id}/unlike/"));
    }

    public function accept($id){
        return $this->query($this->route("/web/friendships/{$id}/approve/"));
    }

    public function reject($id){
        return $this->query($this->route("/web/friendships/{$id}/ignore/"));
    }

    public function getFollowers($userid, $after=null){

        if($after){
             $func = 'after('. $after .',+'. $this->page_count_follow .')';
        } else {
             $func = 'first('. $this->page_count_follow .')';
        }

        return $this->query(
            $this->route("/query/"), [
            'q' => 'ig_user('. $userid .'){followed_by.'.  $func .'{count,page_info{end_cursor,has_next_page},nodes{id,is_verified,followed_by_viewer,requested_by_viewer,full_name,profile_pic_url,username}}}',
        ])->getBody();
    }

    public function getFollows($userid, $after=null){

        if($after){
             $func = 'after('. $after .',+'. $this->page_count_follow .')';
        } else {
             $func = 'first('. $this->page_count_follow .')';
        }

        return $this->query(
            $this->route("/query/"), [
            'q' => 'ig_user('. $userid .'){follows.'.  $func .'{count,page_info{end_cursor,has_next_page},nodes{id,is_verified,followed_by_viewer,requested_by_viewer,full_name,profile_pic_url,username}}}',
        ])->getBody();
    }

    public function getUpdates($userid, $after=null){

        if($after){
             $func = 'after('. $after .',+'. $this->page_count_stuff .')';
        } else {
             $func = 'first('. $this->page_count_stuff .')';
        }

        return $this->query(
            $this->route("/query/"), [
            'q' => 'ig_user('. $userid .'){media.'.  $func .'{count,nodes{caption,code,comments{count},comments_disabled,date,dimensions{height,width},display_src,id,is_video,likes{count},owner{id},thumbnail_src,video_views},page_info}}'
        ])->getBody();
    }

}
