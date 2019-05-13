<?php
require_once "./vendor/autoload.php";
require_once "structures.php";

use am\internet\HttpHelper;

//analisar KEY_ERROR

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
        $comments = $json[1]->data->children;
        $this->extract_comments($comments);

        $mostUpvoted = $this->find_most_upvoted_comment($this->mCommentsList);
        $mostControversial = $this->find_most_controversial_comment($this->mCommentsList);
        $mostAwarded = $this->find_most_awarded_comment($this->mCommentsList);
    }// fetch_from_json

    private function get_json($pUrl) {
        $data = $this->mHttpHelper->http($pUrl)[HttpHelper::KEY_BIN];
        return json_decode($data);
    }

    private $mCommentsList = array();
    private function extract_comments($pJsonComments) {
        foreach ($pJsonComments as $comment){
            $jAwards = $comment->data->total_awards_received;
            $jScore = $comment->data->score;
            $jAuthor = $comment->data->author;
            $jContent = $comment->data->body;
            $jCreated = gmdate("r", $comment->data->created_utc);
            $jReplies = $comment->data->replies != "" ? $comment->data->replies->data->children : null;
            if(is_array($jReplies) && count($jReplies)>0){
                $jNumReplies = count($comment->data->replies->data->children);
                $this->extract_comments($jReplies);
            }else{
                $jNumReplies = 0;
            }
            $this->mCommentsList[] = new RedditComment($jAwards, $jScore, $jNumReplies, $jAuthor, $jContent, $jCreated);
            echo $jAuthor.PHP_EOL;
            echo $jAwards.PHP_EOL; 
            echo $jScore.PHP_EOL;
            echo $jContent.PHP_EOL;
            echo $jCreated.PHP_EOL;         
        }
    }

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
$json = $r->mHttpHelper->http($r->build_query("apexlegends", Category::cTop, Time::tDay))[HttpHelper::KEY_BIN];
$oJson = json_decode($json);
$r->fetch_from_json($oJson);