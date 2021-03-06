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

    public function __construct($uname=null, $password=null)
    {
        $this->username = is_null($uname) ? env('INSTA_USERNAME', '_') : $uname;
        $this->password = is_null($password) ? env('INSTA_PASSWORD', null) : $password;

        $this->client = new \GuzzleHttp\Client();
        //$this->jar = new \GuzzleHttp\Cookie\CookieJar();
        $this->jar = new FileCookieJar('storage/sessions/' . $this->username . '.jar');

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
            return $this->data->entry_data->FeedPage[0]->graphql->user->edge_web_feed_timeline->edges;
        }

        $data = $this->fetch($this->route($uname));
        return $data->entry_data->ProfilePage[0]->user->media->nodes;
    }

    public function query($path, $args=[], $method='POST', $referrer='https://www.instagram.com/'){

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
            'Content-Type' => 'application/x-www-form-urlencoded',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer' => $referrer
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
        return $this->query(
            $this->route("/web/friendships/{$id}/unfollow/"),
            [],
            'POST',
            $this->route('/'.$this->username.'/following/')
        );
    }

    public function like($id){
        return $this->query($this->route("/web/likes/{$id}/like/"));
    }

    public function unlike($id){
        return $this->query($this->route("/web/likes/{$id}/unlike/"));
    }

    public function comment($id, $text){
        return $this->query($this->route("/web/comments/{$id}/add/"), [
            'comment_text' => $text
        ]);
    }

    public function accept($id){
        return $this->query($this->route("/web/friendships/{$id}/approve/"));
    }

    public function reject($id){
        return $this->query($this->route("/web/friendships/{$id}/ignore/"));
    }

    public function getFollowers($userid, $after=null){

        return $this->query(
            $this->route('/graphql/query/?query_id=17851374694183129&variables={"id":"485981610","first":100'.($after ? ',"after":"'.$after.'"' : '').'}')
            [], 'GET')->getBody();
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
