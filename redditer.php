<?php

require_once "./vendor/autoload.php";
require_once "structures.php";
require_once "post.php";
require_once "comment.php";
require_once "utils.php";

use am\internet\HttpHelper;

// TODO: implementar mais estatisticas
// average post score
// average post comments
// average title length ?
// average post body length

// TODO: PERGUNTAR:
// ferramenta para visualizar estatisticas GOOGLE PIE CHARTS
// wordpresser nao encontra am_wordpress_tools DONE
// wordpresser como funciona thumbnail WORDPRESSER_UPLOADBINARY
// wordpresser como funciona datetime TEM QUE SER IXR_DATE

// TODO: implementar procura no subreddit
// https://www.reddit.com/r/apexlegends/search/?q=pathfinder&restrict_sr=1&sort=relevance&t=hour&include_over_18=1

class Redditer {

    private $mHttpHelper;
    private $mJURL = false;
    private $mPostsList = array();
    private $mCommentsList = array();
    private $mQuery = array();

    const REDDIT_URL = "https://www.reddit.com/";
    const SUBREDDIT_BASE = self::REDDIT_URL."r/%s/%s.json?t=%s&limit=%d";
    const SUBREDDIT_SEARCH_BASE = self::REDDIT_URL."r/%s/search.json?restrict_sr=1&q=%s&sort=%s&t=%s&include_over_18=%d";

    public function __construct(){
        $this->mHttpHelper = new HttpHelper("Redditer v1.0");
    }// __construct

    public function on_subreddit($pSubreddit="movies", $pCategory=Category::cHot, $pTime=Time::tDay, $pLimit=100){
        $this->mPostsList = array();
        $this->mCommentsList = array();
        $this->mQuery['subreddit'] = $pSubreddit;
        $this->mQuery['category'] = $pCategory;
        $this->mQuery['time'] = $pTime;
        $this->mQuery['limit'] = $pLimit;
        $this->mQuery['after'] = false;
        return $this;
    }

    public function search($pInput, $pSort="relevance", $pTime=Time::tDay, $pOver18=false){
        $this->mQuery['input'] = $pInput;
        $this->mQuery['sort'] = $pSort;
        $this->mQuery['time'] = $pTime;
        $this->mQuery['over18'] = $pOver18;
        $this->subreddit_build_search_query();
        return $this;
    }

    public function get_posts(){
        if($this->mJURL == false) $this->subreddit_build_query();
        $json = $this->get_json();
        $posts = $json->data->children;
        $after = $json->data->after;
        $postsCreated = 0;
        foreach ($posts as $post){
            $redditPost = new Post($post->data);
            $url = substr($redditPost->postUrl, 0, -1).".json";
            $json = $this->get_json($url);
            if($json != false){
                $postsCreated = $postsCreated + 1;
                $jcomments = $json[1]->data->children;
                $this->extract_comments($jcomments);
                $redditPost->set_comments($this->mCommentsList);
                $this->mCommentsList = array(); // need to clear due to performance issues
                $this->mPostsList[] = $redditPost;
            }else{
                echo "Failed to retrieve json from url: ".$url.PHP_EOL;
            }// if
        }// foreach
        if($postsCreated < $this->mQuery['limit']){
            $this->mQuery['limit'] = $this->mQuery['limit'] - $postsCreated;
            $this->mQuery['after'] = $after;
            $this->subreddit_build_query();
            $this->get_posts();
        }// if
        return $this->mPostsList;
    }// get_posts

    private function extract_comments($pJsonComments) {
        foreach ($pJsonComments as $comment){
            if($comment->kind == "t1"){
                $jReplies = $comment->data->replies != "" ? $comment->data->replies->data->children : null;
                if(is_array($jReplies) && count($jReplies)>0){
                    $jNumReplies = count($comment->data->replies->data->children);
                    $this->extract_comments($jReplies);
                }else{
                    $jNumReplies = 0;
                }// if
                $this->mCommentsList[] = new Comment($comment->data, $jNumReplies);
            }// if
        }// foreach
    }// extract_comment

    private function subreddit_build_search_query(){
        $query = $this->mQuery;
        $url = sprintf(self::SUBREDDIT_SEARCH_BASE, $query['subreddit'], $query['input'],
            $query['sort'], $query['time'], $query['over18']);
        $this->mJURL = $query['after'] == false ? $url : $url."&after=".$query['after'];
    }

    private function subreddit_build_query(){
        $query = $this->mQuery;
        $url = sprintf(self::SUBREDDIT_BASE, $query['subreddit'],
            $query['category'], $query['time'], $query['limit']);
        $this->mJURL = $query['after'] == false ? $url : $url."&after=".$query['after'];
    }

    private function get_json($pUrl=null){
        $url = $pUrl ?? $this->mJURL;
        echo $url.PHP_EOL;
        $result = $this->mHttpHelper->http($url);
        $data = $result[HttpHelper::KEY_BIN];
        $status = $result[HttpHelper::KEY_STATUS];
        if($status['http_code'] != 200){
            return false;
        }// if
        return json_decode($data);
    }// get_json
}// Redditer

$start = microtime(true);
$r = new Redditer();
$array = $r->on_subreddit("apexlegends", Category::cTop, Time::tDay, 200)->get_posts();
$time_elapsed_secs = microtime(true) - $start;
echo $time_elapsed_secs;
//$r->get_statistics();