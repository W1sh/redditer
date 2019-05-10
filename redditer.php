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
    const tday = "day";
    const tWeek = "week";
    const tMonth = "month";
    const tYear = "year";
    const tAll = "all";
}

class Redditer {
    public $mHttpHelper;

    const REDDIT_SEARCH_BASE = "https://www.reddit.com/r/%s/%s.json?t=%s";

    public function __construct(){
        $this->mHttpHelper = new HttpHelper(HttpHelper::USER_AGENT_STRING_MOZ47);
    }//__construct

    public function build_query($pSubreddit, $pCategory, $pTime) : string{
        return sprintf(self::REDDIT_SEARCH_BASE, $pSubreddit, $pCategory, $pTime);
    }
}// redditer

$r = new Redditer();
$json = $r->mHttpHelper->http(sprintf($r::REDDIT_SEARCH_BASE, "apexlegends", "hot", "day"))[HttpHelper::KEY_BIN];
$url = sprintf($r::REDDIT_SEARCH_BASE, "apexlegends", Category::cHot, Time::tday);
echo $url.PHP_EOL;
$oJson = json_decode($json);
var_dump($oJson);