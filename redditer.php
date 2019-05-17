<?php

require_once "./vendor/autoload.php";
require_once "structures.php";
require_once "post.php";
require_once "comment.php";

use am\internet\HttpHelper;

//analisar KEY_ERROR
// postar estatisticas

// TODO: PERGUNTAR:
// ferramenta para visualizar estatisticas
// wordpresser nao encontra am_wordpress_tools DONE
// wordpresser como funciona thumbnail
// wordpresser como funciona datetime

class Redditer {

    public $mHttpHelper;
    private $mPostsList = array();
    private $mCommentsList = array();
    private $mQuery = array(
        'subreddit'=>false,
        'category'=>Category::cHot,
        'time'=>Time::tDay,
        'after'=>false,
        'limit'=>100
    );

    const REDDIT_SEARCH_BASE = "https://www.reddit.com/r/%s/%s.json?t=%s&limit=%d";

    public function __construct($pSubreddit, $pCategory, $pTime, $pAfter, $pLimit){
        $this->mQuery['subreddit'] = $pSubreddit;
        $this->mQuery['category'] = $pCategory;
        $this->mQuery['time'] = $pTime;
        $this->mQuery['after'] = $pAfter;
        $this->mQuery['limit'] = $pLimit;
        $this->mHttpHelper = new HttpHelper("Redditer v1.0");
    }// __construct

    public function set_subreddit($pSubreddit){
        $this->mQuery['subreddit'] = $pSubreddit;
    }// set_subreddit

    public function set_category($pCategory){
        if($pCategory instanceof Category){
            $this->mQuery['category'] = $pCategory;
            return true;
        }
        return false;
    }// set_category

    public function set_time($pTime){
        if($pTime instanceof Time){
            $this->mQuery['time'] = $pTime;
            return true;
        }
        return false;
    }// set_time

    public function set_limit($pLimit){
        if($pLimit > 0){
            $this->mQuery['limit'] = $pLimit;
            return true;
        }
        return false;
    }// set_limit

    public function get_json($pUrl=false){
        if($pUrl == false){
            $pUrl = $this->build_query();
        }
        echo $pUrl.PHP_EOL;
        $data = $this->mHttpHelper->http($pUrl)[HttpHelper::KEY_BIN];
        return json_decode($data);
    }// get_json

    public function get_posts($pJson){
        $posts = $pJson->data->children;
        foreach ($posts as $post){
            $redditPost = new Post($post->data);
            $json = $this->get_json(substr($redditPost->postUrl, 0, -1).".json");
            $jcomments = $json[1]->data->children;
            $this->extract_comments($jcomments);
            $redditPost->set_comments($this->mCommentsList);
            $this->mCommentsList = array(); // need to clear due to performance issues
            $this->mPostsList[] = $redditPost;
        }// foreach
        return $this->mPostsList;
    }// get_posts

    public function get_statistics() : string{
        if(count($this->mPostsList) == 0){
            return "Failed to evaluate posts. No posts were found.";
        }
        $totalScore = 0;
        $totalNumComments = 0;
        $totalAwards = 0;
        foreach($this->mPostsList as $post){
            $totalScore = $totalScore + $post->score;
            $totalNumComments = $totalNumComments + $post->numComments;
            $totalAwards = $totalAwards + $post->awards;
        }// foreach
        return sprintf("Evaluated %d posts from %s totaling %d score, with %d comments and %d awards received",
            count($this->mPostsList), $this->mPostsList[0]->subreddit, $totalScore, $totalNumComments, $totalAwards);
        /*print_r($this->frequency_map($this->mPostsList, "title", 20));
        print_r($this->frequency_map($this->mPostsList, "body", 20));
        print_r($this->frequency_map($this->mCommentsList, "body", 20));*/
    }// get_statistics

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

    private function frequency_map($pList, $pParam, $pMapSize){
        $strings = array();
        foreach ($pList as $element) {
            $stringAsArray = explode(" ", $element->$pParam);
            $strings = array_merge($strings, $stringAsArray);
        }
        $frequencyMap = array_count_values($strings);
        arsort($frequencyMap);
        return array_slice($frequencyMap, 0, $pMapSize);
    }// frequency_map

    private function build_query() {
        $formattedURL = sprintf(self::REDDIT_SEARCH_BASE, $this->mQuery['subreddit'],
            $this->mQuery['category'], $this->mQuery['time'], $this->mQuery['limit']);
        if($this->mQuery['after'] == false){
            return $formattedURL;
        }else{
            return $formattedURL."&after=".$this->mQuery['after'];
        }
    }// build_query
}// Redditer

$start = microtime(true);
$r = new Redditer("apexlegends", Category::cTop, Time::tDay, false, 5);
$json = $r->get_json();
$array = $r->get_posts($json);
$stats = $r->get_statistics();
echo $stats;
$time_elapsed_secs = microtime(true) - $start;
echo $time_elapsed_secs;
//$r->get_statistics();