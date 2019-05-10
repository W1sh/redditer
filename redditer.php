<?php
require_once "./vendor/autoload.php";

use am\internet\HttpHelper;

class Redditer {
    private $mHttpHelper;

    const REDDIT_SEARCH_BASE = "https://www.reddit.com/search?q=";

    public function __construct(){
        $this->mHttpHelper = new HttpHelper(HttpHelper::USER_AGENT_STRING_MOZ47);
    }//__construct


}// redditer

$r = new Redditer();