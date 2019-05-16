<?php
require_once "./vendor/autoload.php";
require_once "structures.php";

use am\internet\HttpHelper;

//analisar KEY_ERROR

// TODO: PERGUNTAR:
// ferramenta para visualizar estatisticas
// remover preposições do mapa
// wordpresser nao encontra am_wordpress_tools DONE
// wordpresser como funciona thumbnail

class Redditer {

    public $mHttpHelper;
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
        $this->mHttpHelper = new HttpHelper(HttpHelper::USER_AGENT_STRING_MOZ47);
    }// __construct

    private function build_query() {
        $formattedURL = sprintf(self::REDDIT_SEARCH_BASE,
            $this->mQuery['subreddit'],
            $this->mQuery['category'],
            $this->mQuery['time'],
            $this->mQuery['limit']);
        if($this->mQuery['after'] == false){
            echo $formattedURL.PHP_EOL;
            return $formattedURL;
        }else{
            return $formattedURL."&after=".$this->mQuery['after'];
        }
    }// build_query

    public function get_posts($pJson){
        $posts = $pJson->data->children;
        foreach ($posts as $post){
            $redditPost = $this->extract_post($post);

            $jPostUrl = substr($redditPost->contentUrl, 0, -1).".json";
            echo $jPostUrl.PHP_EOL;
            $json = $this->get_json_from_url($jPostUrl);
            $comments = $json[1]->data->children;
            $this->extract_comments($comments);

            $mostUpvoted = $this->find_most_upvoted_comment($this->mCommentsList);
            $mostControversial = $this->find_most_controversial_comment($this->mCommentsList);
            $mostAwarded = $this->find_most_awarded_comment($this->mCommentsList);

            $redditPost->set_comments($mostUpvoted, $mostControversial, $mostAwarded);
            $this->mCommentsList = array(); // need to clear due to performance issues
            $this->mPostsList[] = $redditPost;
        }// foreach
        return $this->mPostsList;
    }// get_posts

    public function get_json() {
        $jurl = $this->build_query();
        return $this->get_json_from_url($jurl);
    }// get_json

    public function get_json_from_url($pUrl) {
        $data = $this->mHttpHelper->http($pUrl)[HttpHelper::KEY_BIN];
        return json_decode($data);
    }// get_json_from_url

    public function get_statistics(){
        var_dump($this->frequency_map($this->mPostsList, "title", 20));
        var_dump($this->frequency_map($this->mPostsList, "body", 20));
        var_dump($this->frequency_map($this->mCommentsList, "body", 20));
    }// get_statistics

    private $mPostsList = array();
    private function extract_post($pJson) {
        $jTitle = $pJson->data->title;
        $jBody = $pJson->data->selftext;
        $jScore = $pJson->data->score;
        $jAwards = $pJson->data->total_awards_received;
        $jAuthor = $pJson->data->author;
        $jContentUrl = "https://www.reddit.com".$pJson->data->permalink;
        $jPostUrl = $pJson->data->url;
        $jOver18 = $pJson->data->over_18;
        $jSpoiler = $pJson->data->spoiler;
        $jThumbnail = $pJson->data->thumbnail;
        $jSubreddit = $pJson->data->subreddit_name_prefixed;
        $jCreated = gmdate("d-m-Y h:m", $pJson->data->created_utc); 
        $builtTitle = $this->build_title($jTitle, $jSubreddit, $jOver18, $jSpoiler);
        return new RedditPost(
            $builtTitle, $jBody, $jScore, $jAuthor, $jAwards, $jPostUrl, $jContentUrl, $jCreated, $jThumbnail, $jSubreddit);
    }// extract_json

    private $mCommentsList = array();
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

    private function build_title($pTitle, $pSubreddit, $pOver18=false, $pSpoiler=false) : string{
        if($pSpoiler && $pOver18){
            return sprintf("(%s | %s) (%s) %s", $pOver18, $pSpoiler, $pSubreddit, $pTitle);
        }else if($pSpoiler || $pOver18){
            return sprintf("(%s) (%s) %s", ($pOver18 ? $pOver18 : $pSpoiler), $pSubreddit, $pTitle);
        }
        return $pTitle;
    }// build_title
}// Redditer

$start = microtime(true);
$r = new Redditer("apexlegends", Category::cTop, Time::tDay, false, 10);
$json = $r->get_json();
$r->get_posts($json);
$time_elapsed_secs = microtime(true) - $start;
echo $time_elapsed_secs;
//$r->get_statistics();