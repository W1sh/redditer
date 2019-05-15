<?php

abstract class Category{
    const cHot = "hot";
    const cNew = "new";
    const cControversial = "controversial";
    const cTop = "top";
    const cRising = "rising";
}

abstract class Time{
    const tHour = "hour";
    const tDay = "day";
    const tWeek = "week";
    const tMonth = "month";
    const tYear = "year";
    const tAll = "all";
}

class RedditPost {
    public $title;
    public $body;
    public $score;
    public $author;
    public $awards;
    public $postUrl;
    public $contentUrl;
    public $created;

    public function __construct($pTitle, $pBody, $pScore, $pAuthor, $pAwards, $pPostUrl, $pContentUrl, $pCreated){
        $this->title = $pTitle;
        $this->body = $pBody;
        $this->score = $pScore;
        $this->author = $pAuthor;
        $this->awards = $pAwards;
        $this->postUrl = $pPostUrl;
        $this->contentUrl = $pContentUrl;
        $this->created = $pCreated;
    }
}

class RedditComment {
    public $awards;
    public $score;
    public $replies;
    public $author;
    public $content;
    public $created;

    public function __construct($pAwards, $pScore, $pReplies, $pAuthor, $pContent, $pCreated){
        $this->awards = $pAwards;
        $this->score = $pScore;
        $this->replies = $pReplies;
        $this->author = $pAuthor;
        $this->content = $pContent;
        $this->created = $pCreated;
    } //__construct
} // Comment