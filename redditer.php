<?php
require_once "./vendor/autoload.php";

class Redditer {
    private $mHttpHelper;

    public function __construct(){
        $this->mHttpHelper = new \libs\helpers\am\internet\HttpHelper();
        $this->mHttpHelper = new HttpHelper(HttpHelper::USER_AGENT_STRING_MOZ47//,
            //false
        );
    }//__construct
}// redditer

$r = new Redditer();