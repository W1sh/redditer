<?php
require_once "./vendor/autoload.php";
require_once "structures.php";

use am\internet\HttpHelper;

//analisar KEY_ERROR

class Redditer {

    public $mHttpHelper;
    public $mSubreddit;
    public $mLimit;

    const REDDIT_SEARCH_BASE = "https://www.reddit.com/r/%s/%s.json?t=%s&limit=%d";

    public function __construct(){
        $this->mHttpHelper = new HttpHelper(HttpHelper::USER_AGENT_STRING_MOZ47);
    }//__construct

    public function build_query($pSubreddit, $pCategory=Category::cHot, $pTime=Time::tDay, $pLimit=10) : string{
        $this->mSubreddit = $pSubreddit;
        $this->mLimit = $pLimit;
        return sprintf(self::REDDIT_SEARCH_BASE, $pSubreddit, $pCategory, $pTime, $pLimit);
    }// build_query

    public function fetch_from_json($pJson) {
        $posts = $pJson->data->children;
        $numPosts = $pJson->data->dist;
        if($numPosts < $this->mLimit){
            // TODO: lidar com mais posts dos que mostrados no primeiro json

            /*$jAfter = $pJson->data->after;
            $afterURL = self::REDDIT_SEARCH_BASE.sprintf("&after=%s", $jAfter);
            $json = $this->mHttpHelper->http($this->build_query("apexlegends", Category::cTop, Time::tDay))[HttpHelper::KEY_BIN];
            $oJson = json_decode($json);
            $this->fetch_from_json($oJson);*/
        }
        foreach ($posts as $keys){
            $permalink = "https://www.reddit.com".substr($keys->data->permalink, 0, -1).".json";
            /*echo $keys->data->title;
            //echo $keys->data->selftext;
            //echo $keys->data->selftext_html;
            echo $keys->data->ups; // maybe use ->score instead
            echo $keys->data->total_awards_received;
            echo $keys->data->author;
            echo $keys->data->num_comments;
            echo $keys->data->permalink;
            echo $keys->data->url;
            echo $keys->data->over_18;
            echo $keys->data->spoiler;
            echo $keys->data->created_utc;
            // gmdate ("d-m-Y h:m", $created_utc);*/
        }// foreach
        echo $permalink.PHP_EOL;
        $json = $this->get_json($permalink);
        $post = $json[0]->data->children[0];
        $this->extract_post($post);

        var_dump($this->mPostsList[0]);
        $comments = $json[1]->data->children;
        $this->extract_comments($comments);

        $mostUpvoted = $this->find_most_upvoted_comment($this->mCommentsList);
        $mostControversial = $this->find_most_controversial_comment($this->mCommentsList);
        $mostAwarded = $this->find_most_awarded_comment($this->mCommentsList);

    }// fetch_from_json

    private function get_json($pUrl) {
        $data = $this->mHttpHelper->http($pUrl)[HttpHelper::KEY_BIN];
        return json_decode($data);
    }// get_json

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
        $jCreated = gmdate("d-m-Y h:m", $pJson->data->created_utc); 
        $builtTitle = $this->build_title($jTitle, $jOver18, $jSpoiler);
        $this->mPostsList[] = new RedditPost(
            $builtTitle, $jBody, $jScore, $jAuthor, $jAwards, $jPostUrl, $jContentUrl, $jCreated);
    }


    private $mCommentsList = array();
    private function extract_comments($pJsonComments) {
        foreach ($pJsonComments as $comment){
            $jAwards = $comment->data->total_awards_received;
            $jScore = $comment->data->score;
            $jAuthor = $comment->data->author;
            $jContent = $comment->data->body;
            $jCreated = gmdate("d-m-Y h:m", $comment->data->created_utc);
            $jReplies = $comment->data->replies != "" ? $comment->data->replies->data->children : null;
            if(is_array($jReplies) && count($jReplies)>0){
                $jNumReplies = count($comment->data->replies->data->children);
                $this->extract_comments($jReplies);
            }else{
                $jNumReplies = 0;
            }
            $this->mCommentsList[] = new RedditComment(
                $jAwards, $jScore, $jNumReplies, $jAuthor, $jContent, $jCreated);     
        }// foreach
    }// extract_comments

    private function find_most_awarded_comment($pArrayComments){
        usort($pArrayComments, function ($a, $b){
            return ($a->awards > $b->awards) ? -1 : 1;
        });
        return $pArrayComments[0];
    }//find_most_awarded_comment

    private function find_most_controversial_comment($pArrayComments){
        usort($pArrayComments, function ($a, $b){
            return ($a->replies > $b->replies) ? -1 : 1;
        });
        return $pArrayComments[0];
    }//find_most_controversial_comment

    private function find_most_upvoted_comment($pArrayComments){
        usort($pArrayComments, function ($a, $b){
            return ($a->score > $b->score) ? -1 : 1;
        });
        return $pArrayComments[0];
    }//find_most_upvoted_comment

    private function build_title($pTitle, $pOver18=false, $pSpoiler=false) : string{
        if($pSpoiler && $pOver18){
            return sprintf("(%s | %s) %s", $pOver18, $pSpoiler, $pTitle);
        }else if($pSpoiler || $pOver18){
            return sprintf("(%s) %s", ($pOver18 ? $pOver18 : $pSpoiler), $pTitle);
        }
        return $pTitle;
    }// build_title
}// redditer

$r = new Redditer();
$json = $r->mHttpHelper->http($r->build_query("apexlegends", Category::cTop, Time::tDay))[HttpHelper::KEY_BIN];
$oJson = json_decode($json);
$r->fetch_from_json($oJson);