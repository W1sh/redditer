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