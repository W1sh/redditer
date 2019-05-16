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
        //print_r($this->comments['upvoted']);
        $this->comments['controversial'] = $this->best_comment_by_param("replies");
        //print_r($this->comments['controversial']);
        $this->comments['awarded'] = $this->best_comment_by_param("awards");
        //print_r($this->comments['awarded']);
        //print_r($this->most_engaged_redditor());
        $this->most_liked_redditor();
    }// set_comments

    private function best_comment_by_param($pParam){
        usort($this->comments['all'], function ($a, $b) use ($pParam){
            return ($a->$pParam > $b->$pParam) ? -1 : 1;
        });
        return $this->comments['all'][0];
    }

    private function most_engaged_redditor(){
        return array_slice($this->redditors_frequency_map(), 0, 1);
    }// most_engaged_redditor

    private function most_liked_redditor(){
        $redditors = $this->redditors_frequency_map();
        $totalScoreRedditor = array();
        foreach($redditors as $redditor => $frequency){
            echo $redditor;
            $commentsByRedditor = $this->filter_by_redditor($redditor);
            $totalScore = 0;
            foreach ($commentsByRedditor as $comment){
                $totalScore = $totalScore + $comment->score;
            }
            $totalScoreRedditor[$redditor] = $totalScore;
        }
        arsort($totalScoreRedditor);
        return array_slice($totalScoreRedditor, 0, 1);
    }// most_liked_redditor

    private function most_awarded_redditor(){
        
    }// most_awarded_redditor

    private function filter_by_redditor($pRedditor){
        return array_filter($this->comments['all'], function ($item) use ($pRedditor){
            return $item->author == $pRedditor;
        });
    }// filter_by_redditor

    private function redditors_frequency_map(){
        $redditors = array();
        foreach ($this->comments['all'] as $comment) {
            $redditors[] = $comment->author;
        }
        $frequencyMap = array_count_values($redditors);
        arsort($frequencyMap);
        return $frequencyMap;
    }// redditors_frequency_map

    private function build_title($pTitle, $pSubreddit, $pOver18=false, $pSpoiler=false) : string{
        if($pSpoiler && $pOver18){
            return sprintf("(%s | %s) (%s) %s", $pOver18, $pSpoiler, $pSubreddit, $pTitle);
        }else if($pSpoiler || $pOver18){
            return sprintf("(%s) (%s) %s", ($pOver18 ? $pOver18 : $pSpoiler), $pSubreddit, $pTitle);
        }
        return $pTitle;
    }// build_title
}