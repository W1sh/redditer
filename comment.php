<?php

class Comment {
    public $awards;
    public $score;
    public $replies;
    public $author;
    public $body;
    public $created;

    public function __construct($data, $pNumReplies){
        $this->replies = $pNumReplies;
        $this->awards = $data->total_awards_received;
        $this->score = $data->score;
        $this->author = $data->author;
        $this->body = $data->body;
        $this->created = $data->created_utc;
    }// __construct

    public function __toString(){
        return $this->body."<br><p>Posted by <strong>".$this->author."</strong> - "
            .time_as_pretty_string($this->created)."<br>Awards: ".$this->awards." - Score: "
            .$this->score." - Replies: ".$this->replies."</p>";    
    }// __toString
} // Comment