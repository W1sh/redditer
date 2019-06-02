<?php

class Post {
    public $id;
    public $title;
    public $body;
    public $score;
    public $author;
    public $awards;
    public $postUrl;
    public $contentUrl = array();
    public $created;
    public $timePassed;
    public $subreddit;
    public $numComments;
    public $comments = array();
    private function __construct(){

    }
    public static function newPostArray($array)
    {
        $obj = new Post();
        $obj->id = $array['PostId'];
        $obj->title = $array['Title'];
        $obj->body = $array['Body'];
        $obj->score = $array['Score'];
        $obj->author = $array['Redditor'];
        $obj->awards = $array['Awards']; 
        $obj->postUrl = $array['PostUrl'];
        $obj->contentUrl['is_video'] = $array['IsVideo'];
        $obj->contentUrl['url'] = $array['Url'];
        $obj->contentUrl['image_id'] = $array['ImageId'];
        $obj->created = $array['Created'];
        $obj->timePassed = time_as_pretty_string($obj->created);
        $obj->subreddit = $array['Subreddit'];
        $obj->numComments = $array['NumComments'];
        $obj->comments = $array['comments'];
        return $obj;
    }
    public static function newPostData($data){
        $obj = new Post();
        $obj->id = $data->id;
        $jOver18 = $data->over_18;
        $jSpoiler = $data->spoiler;
        $jTitle = $data->title;
        $obj->title = $data->title;
        $obj->body = $data->selftext;
        $obj->score = $data->score;
        $obj->author = $data->author;
        $obj->awards = $data->total_awards_received;
        $obj->postUrl = "https://www.reddit.com".$data->permalink;
        if(strpos($data->post_hint, "video") !== false){
            $obj->contentUrl['is_video'] = true;
            $obj->contentUrl['url'] = $data->media->reddit_video->fallback_url;
            $obj->contentUrl['image_id'] = false;
        }else{
            if($data->thumbnail != "self"){
                $obj->contentUrl['is_video'] = false;
                $obj->contentUrl['url'] = str_replace("amp;", "", $data->preview->images[0]->source->url);
                $obj->contentUrl['image_id'] = false;
            }else{
                $obj->contentUrl['is_video'] = false;
                $obj->contentUrl['url'] = false;
                $obj->contentUrl['image_id'] = false;
            }
        }
        $obj->timePassed = time_as_pretty_string($data->created_utc);
        $obj->created = $data->created_utc;
        $obj->subreddit = $data->subreddit_name_prefixed;
        $obj->numComments = $data->num_comments;
        $obj->title = $obj->build_title($jTitle, $obj->subreddit, $jOver18, $jSpoiler);
        return $obj;
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

    public function as_json($pIsAssoc){
        return json_encode(self, $pIsAssoc);
    }// as_json

    private function best_comment_by_param($pParam){
        usort($this->comments, function ($a, $b) use ($pParam){
            return ($a->$pParam > $b->$pParam) ? -1 : 1;
        });
        return $this->comments[0];
    }// best_comment_by_param

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
        $string = "<br><p>Posted by <strong>".$this->author."</strong> on ".$this->subreddit.
        " - ".$this->timePassed."</p><br>";
        if($this->contentUrl){
            $string .= $this->contentUrl['is_video'] ? "<video width=\"100%\" height=\"auto\" controls><source src="
                .$this->contentUrl['url']." type=\"video/mp4\"></video>" : "";
        }
        $string .= $this->body."<br><br>";
        $string .= "<h4>The most Upvoted Comment</h4><p>".$comments['most_liked']->__toString()."</p><br>";
        $string .= "<h4>The most Awarded Comment</h4><p>".$comments['most_awarded']->__toString()."</p><br>";
        $string .= "<h4>The most Controversial Comment</h4><p>".$comments['most_controversial']->__toString()."</p>";
        return $string;
    }// __toString
}