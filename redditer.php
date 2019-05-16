<?php

require_once "./vendor/autoload.php";
require_once "structures.php";
require_once "post.php";

use am\internet\HttpHelper;

//analisar KEY_ERROR

// TODO: PERGUNTAR:
// ferramenta para visualizar estatisticas
// remover preposições do mapa
// wordpresser nao encontra am_wordpress_tools DONE
// wordpresser como funciona thumbnail

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

    public function get_json($pUrl=false){
        if($pUrl == false){
            $pUrl = $this->build_query();
        }
        echo $pUrl;
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

            $mostUpvoted = $this->find_most_upvoted_comment($this->mCommentsList);
            $mostControversial = $this->find_most_controversial_comment($this->mCommentsList);
            $mostAwarded = $this->find_most_awarded_comment($this->mCommentsList);

            $redditPost->set_comments($mostUpvoted, $mostControversial, $mostAwarded);
            $this->mCommentsList = array(); // need to clear due to performance issues
            $this->mPostsList[] = $redditPost;
        }// foreach
        return $this->mPostsList;
    }// get_posts

    public function get_statistics(){
        var_dump($this->frequency_map($this->mPostsList, "title", 20));
        var_dump($this->frequency_map($this->mPostsList, "body", 20));
        var_dump($this->frequency_map($this->mCommentsList, "body", 20));
    }// get_statistics

    private function extract_comments($pJsonComments) {
        foreach ($pJsonComments as $comment){
            if($comment->kind == "t1"){
                $jAwards = $comment->data->total_awards_received;
                $jScore = $comment->data->score;
                $jAuthor = $comment->data->author;
                $jBody = $comment->data->body;
                $jCreated = gmdate("d-m-Y h:m", $comment->data->created_utc);
                $jReplies = $comment->data->replies != "" ? $comment->data->replies->data->children : null;
                if(is_array($jReplies) && count($jReplies)>0){
                    $jNumReplies = count($comment->data->replies->data->children);
                    $this->extract_comments($jReplies);
                }else{
                    $jNumReplies = 0;
                }
                $this->mCommentsList[] = new RedditComment(
                    $jAwards, $jScore, $jNumReplies, $jAuthor, $jBody, $jCreated);     
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

    private function find_most_awarded_comment($pArrayComments){
        usort($pArrayComments, function ($a, $b){
            return ($a->awards > $b->awards) ? -1 : 1;
        });
        return $pArrayComments[0];
    }// find_most_awarded_comment

    private function find_most_controversial_comment($pArrayComments){
        usort($pArrayComments, function ($a, $b){
            return ($a->replies > $b->replies) ? -1 : 1;
        });
        return $pArrayComments[0];
    }// find_most_controversial_comment

    private function find_most_upvoted_comment($pArrayComments){
        usort($pArrayComments, function ($a, $b){
            return ($a->score > $b->score) ? -1 : 1;
        });
        return $pArrayComments[0];
    }// find_most_upvoted_comment

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
$r = new Redditer("apexlegends", Category::cTop, Time::tDay, false, 50);
$json = $r->get_json();
$r->get_posts($json);
$time_elapsed_secs = microtime(true) - $start;
echo $time_elapsed_secs;
//$r->get_statistics();