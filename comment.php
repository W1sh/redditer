<?php

class Comment {
    public $id;
    public $awards;
    public $score;
    public $replies;
    public $author;
    public $body;
    public $created;

    private function __construct(){

    }
    public static function newCommentData($data, $pNumReplies){
        $obj = new Comment(); 
        $obj->id = $data->id;
        $obj->replies = $pNumReplies;
        $obj->awards = $data->total_awards_received;
        $obj->score = $data->score;
        $obj->author = $data->author;
        $obj->body = $data->body;
        $obj->created = $data->created_utc;
        return $obj;
    }// __construct

    public static function newCommentArray($array) {
        $obj = new Comment(); 
        $obj->id = $array['CommentsId'];
        $obj->replies = $array['Replies'];
        $obj->awards = $array['Awards'];
        $obj->score = $array['Score'];
        $obj->author = $array['Redditor'];
        $obj->body = $array['Body'];
        $obj->created = $array['Created'];
        return $obj;
    }
    public function __toString(){
        return $this->body."<br><p>Posted by <strong>".$this->author."</strong> - "
            .time_as_pretty_string($this->created)."<br>Awards: ".$this->awards." - Score: "
            .$this->score." - Replies: ".$this->replies."</p>";    
    }// __toString
} // Comment