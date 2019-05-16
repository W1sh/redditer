<?php

class Post {
    public $title;
    public $body;
    public $score;
    public $author;
    public $awards;
    public $postUrl;
    public $contentUrl;
    public $created;
    public $thumbnail;
    public $subreddit;
    public $comments = array(
        'all'=>array(),
        'upvoted'=>false,
        'controversial'=>false,
        'awarded'=>false
    );

    public function __construct($data){
        $jOver18 = $data->over_18;
        $jSpoiler = $data->spoiler;
        $jTitle = $data->title;
        $this->title = $data->title;
        $this->body = $data->selftext;
        $this->score = $data->score;
        $this->author = $data->author;
        $this->awards = $data->total_awards_received;
        $this->postUrl = "https://www.reddit.com".$data->permalink;
        $this->contentUrl = $data->url;
        $this->created = gmdate("d-m-Y h:m:s", $data->created_utc);
        $this->thumbnail = $data->thumbnail;
        $this->subreddit = $data->subreddit_name_prefixed;
        $this->title = $this->build_title($jTitle, $this->subreddit, $jOver18, $jSpoiler);
    }// __construct

    public function set_comments($pCommentList){
        $this->comments['all'] = $pCommentList;
        $this->comments['upvoted'] = $this->most_upvoted_comment();
        $this->comments['controversial'] = $this->most_controversial_comment();
        $this->comments['awarded'] = $this->most_awarded_comment();
    }// set_comments

    private function most_awarded_comment(){
        usort($this->comments['all'], function ($a, $b){
            return ($a->awards > $b->awards) ? -1 : 1;
        });
        return $this->comments['all'][0];
    }// find_most_awarded_comment

    private function most_controversial_comment(){
        usort($this->comments['all'], function ($a, $b){
            return ($a->replies > $b->replies) ? -1 : 1;
        });
        return $this->comments['all'][0];
    }// find_most_controversial_comment

    private function most_upvoted_comment(){
        usort($this->comments['all'], function ($a, $b){
            return ($a->score > $b->score) ? -1 : 1;
        });
        return $this->comments['all'][0];
    }// find_most_upvoted_comment

    private function most_engaged_redditor(){

    }// most_engaged_redditor

    private function most_liked_redditor(){

    }// most_liked_redditor

    private function most_awarded_redditor(){
        
    }// most_awarded_redditor

    private function build_title($pTitle, $pSubreddit, $pOver18=false, $pSpoiler=false) : string{
        if($pSpoiler && $pOver18){
            return sprintf("(%s | %s) (%s) %s", $pOver18, $pSpoiler, $pSubreddit, $pTitle);
        }else if($pSpoiler || $pOver18){
            return sprintf("(%s) (%s) %s", ($pOver18 ? $pOver18 : $pSpoiler), $pSubreddit, $pTitle);
        }
        return $pTitle;
    }// build_title
}