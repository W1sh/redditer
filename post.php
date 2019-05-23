<?php

// get avg comment score

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
    public $numComments;
    public $comments = array();

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
        //$this->created = gmdate("Y-m-d-G-i-s", $data->created_utc);
        $this->created = $data->created_utc;
        if($data->thumbnail != "self"){
            $this->thumbnail = str_replace("amp;", "", $data->preview->images[0]->source->url);
        }else{
            $this->thumbnail = false;
        }
        $this->subreddit = $data->subreddit_name_prefixed;
        $this->numComments = $data->num_comments;
        $this->title = $this->build_title($jTitle, $this->subreddit, $jOver18, $jSpoiler);
    }// __construct

    public function set_comments($pCommentList){
        $this->comments = $pCommentList;
    }// set_comments

    public function comments_statistics() : array{
        $result = array(
            'most_liked'=>$this->best_comment_by_param("score"),
            'most_controversial'=>$this->best_comment_by_param("replies"),
            'most_awarded'=>$this->best_comment_by_param("awards"),
            'most_used_words'=>words_frequency_map($this->comments, "body", 30)
        );// array
        return $result;     
    }// comments_statistics

    public function engagement_statistics() : array{
        $redditors = $this->redditors_frequency_map();
        $totalAwardsRedditors = array();
        $totalScoreRedditors = array();
        foreach($redditors as $redditor => $frequency){
            $commentsByRedditor = $this->filter_by_redditor($redditor);
            $totalAwards = 0;
            $totalScore = 0;
            foreach ($commentsByRedditor as $comment){
                $totalAwards = $totalAwards + $comment->awards;
                $totalScore = $totalScore + $comment->score;
            }// foreach
            $totalAwardsRedditors[$redditor] = $totalAwards;
            $totalScoreRedditors[$redditor] = $totalScore;
        }// foreach
        arsort($totalAwardsRedditors);
        arsort($totalScoreRedditors);
        
        $result = array(
            'most_engaged'=>array_slice($redditors, 0, 1),
            'most_liked'=>array_slice($totalScoreRedditors, 0, 1),
            'most_awarded'=>array_slice($totalAwardsRedditors, 0, 1)
        );// array
        return $result;
    }// engagement_statistics

    public function as_json($pIsAssoc){
        return json_encode(self, $pIsAssoc);
    }// as_json

    private function best_comment_by_param($pParam){
        usort($this->comments, function ($a, $b) use ($pParam){
            return ($a->$pParam > $b->$pParam) ? -1 : 1;
        });
        return $this->comments[0];
    }// best_comment_by_param

    private function filter_by_redditor($pRedditor){
        return array_filter($this->comments, function ($item) use ($pRedditor){
            return $item->author == $pRedditor;
        });
    }// filter_by_redditor

    private function redditors_frequency_map(){
        $redditors = array();
        foreach ($this->comments as $comment) {
            $redditors[] = $comment->author;
        }// foreach
        $frequencyMap = array_count_values($redditors);
        arsort($frequencyMap);
        return $frequencyMap;
    }// redditors_frequency_map

    private function build_title($pTitle, $pSubreddit, $pOver18=false, $pSpoiler=false) : string{
        if($pSpoiler && $pOver18){
            return sprintf("(%s | %s) (%s) %s", $pOver18, $pSpoiler, $pSubreddit, $pTitle);
        }else if($pSpoiler || $pOver18){
            return sprintf("(%s) (%s) %s", ($pOver18 ? $pOver18 : $pSpoiler), $pSubreddit, $pTitle);
        }// if
        return $pTitle;
    }// build_title
   
    public function __toString(){
        $comments=$this->comments_statistics();
        return $this->body."<br><p>Posted by <strong>".$this->author."</strong> on ".$this->subreddit.
            " - ".time_as_pretty_string($this->created)."</p><br><br>
        <h4>The most Upvoted Comment</h4><p>".$comments['most_liked']->__toString()."</p><br>
        <h4>The most Awarded Comment</h4><p>".$comments['most_awarded']->__toString()."</p><br>
        <h4>The most Controversial Comment</h4><p>".$comments['most_controversial']->__toString()."</p>";    
    }// __toString
}