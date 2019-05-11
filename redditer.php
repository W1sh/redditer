<?php
require_once "./vendor/autoload.php";

use am\internet\HttpHelper;

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

class Redditer {
    public $mHttpHelper;

    const REDDIT_SEARCH_BASE = "https://www.reddit.com/r/%s/%s.json?t=%s&limit=%d";

    public function __construct(){
        $this->mHttpHelper = new HttpHelper(HttpHelper::USER_AGENT_STRING_MOZ47);
    }//__construct

    public function build_query($pSubreddit, $pCategory=Category::cHot, $pTime=Time::tDay, $pLimit=10) : string{
        return sprintf(self::REDDIT_SEARCH_BASE, $pSubreddit, $pCategory, $pTime, $pLimit);
    }// build_query

    public function fetch_from_json($pJson) {
        $posts = $pJson->data->children;
        foreach ($posts as $keys){
            echo $keys->data->title;
            //echo $keys->data->selftext;
            echo $keys->data->ups;
            echo $keys->data->total_awards_received;
            echo $keys->data->author;
            echo $keys->data->num_comments;
            echo $keys->data->permalink;
            echo $keys->data->url;
            echo $keys->data->over_18;
            echo $keys->data->spoiler;
        }
    }// fetch_from_json

    private function build_title($pTitle, $pOver18=false, $pSpoiler=false) : string{
        if($pSpoiler && $pOver18){
            return sprintf("(%s | %s) %s", $pOver18, $pSpoiler, $pTitle);
        }else if($pSpoiler || $pOver18){
            return sprintf("(%s) %s", ($pOver18 ? $pOver18 : $pSpoiler), $pTitle);
        }
        return $pTitle;
    }// build_title
}// redditer

$r = new Redditer();
$json = $r->mHttpHelper->http($r->build_query("apexlegends", Category::cTop, Time::tAll))[HttpHelper::KEY_BIN];
$oJson = json_decode($json);
$r->fetch_from_json($oJson);