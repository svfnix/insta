<?php
namespace App\Console;

class Instagram {

    public $data;
    private $client;
    private $jar;

    public $userid;
    public $username;
    private $password;

    public $page_count = 100;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
        $this->jar = new \GuzzleHttp\Cookie\CookieJar();

        $this->username = 'svfnix';
        $this->password = base64_decode('YHMsdm5saw==');
        $this->userid = '485981610';
    }

    public function route($path=null){
        return "https://www.instagram.com/" . ltrim($path, '/');
    }

    public function fetch($url){
        $response = $this->client->request('GET', $url, [
            'cookies' => $this->jar
        ]);

        preg_match('/\<script type=\"text\/javascript\"\>window\.\_sharedData \= (\{.*?\})\;\<\/script\>/Si', $response->getBody(), $matchs);
        $this->data = json_decode($matchs[1]);
    }

    public function getCsrfToken(){

        $this->fetch(
            $this->route()
        );

        return $this->data->config->csrf_token;
    }

    public function getUserID($uname){

        $this->fetch(
            $this->route("/{$uname}/")
        );

        return $this->data->entry_data->ProfilePage[0]->user->id;
    }

    public function query($path, $args=[]){

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
        return $this->client->request('POST', $path, $args);
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

    public function getFollowers($userid, $after=null){

        if($after){
            $page_tpl = 'after('. $after .',+'. $this->page_count .')';
        } else {
            $page_tpl = 'first('. $this->page_count .')';
        }

        return $this->query(
            $this->route("/query/"), [
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
            $this->route("/query/"), [
            'q' => 'ig_user('. $userid .'){follows.'. $page_tpl .'{count,page_info{end_cursor,has_next_page},nodes{id,is_verified,followed_by_viewer,requested_by_viewer,full_name,profile_pic_url,username}}}',
        ])->getBody();
    }

}
