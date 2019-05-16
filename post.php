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
        $this->comments['upvoted'] = $this->best_comment_by_param("score");
        print_r($this->comments['upvoted']);
        $this->comments['controversial'] = $this->best_comment_by_param("replies");
        print_r($this->comments['controversial']);
        $this->comments['awarded'] = $this->best_comment_by_param("awards");
        print_r($this->comments['awarded']);
        print_r($this->most_engaged_redditor());
    }// set_comments

    private function best_comment_by_param($pParam){
        usort($this->comments['all'], function ($a, $b) use ($pParam){
            return ($a->$pParam > $b->$pParam) ? -1 : 1;
        });
        return $this->comments['all'][0];
    }

    private function most_engaged_redditor(){
        $redditors = array();
        foreach ($this->comments['all'] as $comment) {
            $redditors[] = $comment->author;
        }
        $frequencyMap = array_count_values($redditors);
        arsort($frequencyMap);
        return array_slice($frequencyMap, 0, 1);
    }// most_engaged_redditor

    private function most_liked_redditor(){

    }// most_liked_redditor

    private function most_awarded_redditor(){
        
    }// most_awarded_redditor

    /*private function filter_by_redditor($pRedditor){
        return array_filter($this->comments['all'], function ($item) use ($pRedditor){
            return $item->author = $pRedditor;
        }, ARRAY_FILTER_USE_KEY);
    }*/

    private function frequency_map($pList, $pParam, $pMapSize){
        $redditors = array();
        foreach ($pList as $element) {
            $redditors[] = $element->author;
        }
        $frequencyMap = array_count_values($redditors);
        arsort($frequencyMap);
        return array_slice($frequencyMap, 0, $pMapSize);
    }// frequency_map

    private function build_title($pTitle, $pSubreddit, $pOver18=false, $pSpoiler=false) : string{
        if($pSpoiler && $pOver18){
            return sprintf("(%s | %s) (%s) %s", $pOver18, $pSpoiler, $pSubreddit, $pTitle);
        }else if($pSpoiler || $pOver18){
            return sprintf("(%s) (%s) %s", ($pOver18 ? $pOver18 : $pSpoiler), $pSubreddit, $pTitle);
        }
        return $pTitle;
    }// build_title
}