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
        $this->created = gmdate("d-m-Y h:m", $data->created_utc);
        $this->thumbnail = $data->thumbnail;
        $this->subreddit = $data->subreddit_name_prefixed;
        $this->title = $this->build_title($jTitle, $this->subreddit, $jOver18, $jSpoiler);
    }// __construct

    public function set_comments($pMostUpvoted, $pMostControversial, $pMostAwarded){
        $this->comments['upvoted'] = $pMostUpvoted;
        $this->comments['controversial'] = $pMostControversial;
        $this->comments['awarded'] = $pMostAwarded;
    }// set_comments

    private function build_title($pTitle, $pSubreddit, $pOver18=false, $pSpoiler=false) : string{
        if($pSpoiler && $pOver18){
            return sprintf("(%s | %s) (%s) %s", $pOver18, $pSpoiler, $pSubreddit, $pTitle);
        }else if($pSpoiler || $pOver18){
            return sprintf("(%s) (%s) %s", ($pOver18 ? $pOver18 : $pSpoiler), $pSubreddit, $pTitle);
        }
        return $pTitle;
    }// build_title
}