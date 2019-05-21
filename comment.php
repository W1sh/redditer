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
        $this->created = gmdate("d-m-Y h:m:s", $data->created_utc);
    }// __construct
    public function __toString()
    {
        return $this->body."<br><p>Author: ".$this->author."<br>Awards: ".$this->awards." - Score: ".$this->score." - Replies: ".$this->replies."</p>";    
        
    }
} // Comment