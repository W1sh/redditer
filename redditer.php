<?php
require_once "./vendor/autoload.php";
require_once "structures.php";

use am\internet\HttpHelper;

class Redditer {
    public $mHttpHelper;

    const REDDIT_SEARCH_BASE = "https://www.reddit.com/r/%s/%s.json?t=%s&limit=%d";

    public function __construct(){
        $this->mHttpHelper = new HttpHelper(HttpHelper::USER_AGENT_STRING_MOZ47);
    }//__construct

    public function build_query($pSubreddit, $pCategory=Category::cHot, $pTime=Time::tDay, $pLimit=10) : string{
        return sprintf(self::REDDIT_SEARCH_BASE, $pSubreddit, $pCategory, $pTime, $pLimit);
    }// build_query

    public function fetch_from_json($pJson) {
        $posts = $pJson->data->children;
        foreach ($posts as $keys){
            $permalink = "https://www.reddit.com".substr($keys->data->permalink, 0, -1).".json";
            /*echo $keys->data->title;
            //echo $keys->data->selftext;
            //echo $keys->data->selftext_html;
            echo $keys->data->ups;
            echo $keys->data->total_awards_received;
            echo $keys->data->author;
            echo $keys->data->num_comments;
            echo $keys->data->permalink;
            echo $keys->data->url;
            echo $keys->data->over_18;
            echo $keys->data->spoiler;
            echo $keys->data->created_utc;
            // gmdate ("r", $created_utc);*/
        }
        echo $permalink.PHP_EOL;
        $json = $this->get_json($permalink);
        $a = $this->get_comments($json);
        $comments = $json[1]->data->children;
        /*$commentsArray = array();
        array_pop($comments);
        foreach ($comments as $keys){
            $cAwards = $keys->data->total_awards_received;
            $cScore = $keys->data->score;
            //$cReplies = $this->find_number_of_replies($keys->data->replies->data->children);
            $cAuthor = $keys->data->author;
            $cContent = $keys->data->body;
            $cCreated = $keys->data->created_utc;
            $commentsArray[] = new RedditComment($cAwards, $cScore, $cReplies, $cAuthor, $cContent, $cCreated);
        }
        echo sizeof($commentsArray);*/
        //echo $commentsJSON[1]->data->children[0]->body;
    }// fetch_from_json

    private function get_json($pUrl) {
        $json = $this->mHttpHelper->http($pUrl)[HttpHelper::KEY_BIN];
        return json_decode($json);
    }

    private function get_comments($pJson) : array {
        $aComments = array();
        $comments = $pJson[1]->data->children;
        foreach ($comments as $comment){
            if(isset($comment->data->replies->data->children)){
                // existem replies, continuar loop
            }else {
                // nao ha mais replies, criar comment e sair ? (onde fica o comment)
            }
            $cAwards = $comment->data->total_awards_received;
            echo $cAwards.PHP_EOL;
            $cScore = $comment->data->score;
            echo $cScore.PHP_EOL;
            $cReplies = sizeof($comment->data->replies->data->children);
            echo is_array($comment->data->replies->data->children).PHP_EOL;
            echo $cReplies.PHP_EOL;
            $cAuthor = $comment->data->author;
            echo $cAuthor.PHP_EOL;
            $cContent = $comment->data->body;
            echo $cContent.PHP_EOL;
            $cCreated = $comment->data->created_utc;
            echo $cCreated.PHP_EOL;
            $aComments[] = new RedditComment($cAwards, $cScore, $cReplies, $cAuthor, $cContent, $cCreated);
        }
        return $aComments;
    }

    private function find_number_of_replies($pReplies) : int {
        return 0;
    }//find_number_of_replies

    private function find_most_awarded_comment($pArrayComments) : RedditComment{
        return RedditComment;
    }//find_most_awarded_comment

    private function find_most_controversial_comment($pArrayComments) : RedditComment{
        return RedditComment;
    }//find_most_controversial_comment

    private function find_most_upvoted_comment($pArrayComments) : RedditComment{
        return RedditComment;
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
$json = $r->mHttpHelper->http($r->build_query("apexlegends", Category::cTop, Time::tAll))[HttpHelper::KEY_BIN];
$oJson = json_decode($json);
$r->fetch_from_json($oJson);