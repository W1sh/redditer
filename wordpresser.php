<?php

use am\internet\HttpHelper;
//use twitter_bot\AmTwitterBot;

require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/libs/wordpresser/am_wordpress_tools.php";
require_once __DIR__."/redditer.php";
require_once __DIR__."/Db.php";
require_once __DIR__."/libs/twitter_bot/AmTwitterBot.php";
require_once "secrets.php";
require_once "utils.php";

date_default_timezone_set("Europe/Lisbon");

define ("BLOG_USER", $WORDPRESS_SECRETS['user']);
define ("BLOG_PASS", $WORDPRESS_SECRETS['password']);
define ("BLOG_URL", $WORDPRESS_SECRETS['url']);
define ("BLOG_XMLRPC", BLOG_URL."xmlrpc.php");
define ("TWITTER_SECRETS", $TWITTER_SECRETS);

function post_multiple_to_wordpress($posts,$allowComments=true,$allowPings=true){
    foreach($posts as $post){
        post_to_wordpress($post, $allowComments, $allowPings);
    }
}// post_multiple_to_wordpress

function post_to_wordpress($post,$allowComments=true,$allowPings=true) {
    echo BLOG_USER.PHP_EOL;
    echo BLOG_PASS.PHP_EOL;
    echo BLOG_XMLRPC.PHP_EOL;
    $helper = new HttpHelper();
    if($post->contentUrl['is_video'] == false){
        $bReachableUrl = $helper->isUrlReachable($post->contentUrl['url']);
    }
    if($bReachableUrl){
        $filename = download_thumbnail($post->contentUrl['url'], "temp001");
        $data = wordpress_uploadBinary(__DIR__."/".$filename,BLOG_USER,BLOG_PASS,BLOG_XMLRPC);
        $post->contentUrl['image_id'] = $data['id'];
        unlink(__DIR__."/".$filename);// destroy temp image file
    }
    wordpress_postToBlog (
        $post->title,    // title
        $post->__toString(),    // body
        array("reddit", substr($post->subreddit, 2)),    // categorias
        "wordpresser, redditer, bot",    // keywords
        $post->contentUrl['is_video'] == false ? $post->contentUrl['image_id'] : null,    // featuredImageId
        build_time($post->created),    // date_created
        $allowComments,
        $allowPings,
        BLOG_USER,    // user
        BLOG_PASS,    // pass
        BLOG_XMLRPC);    // xmlrpc 
    //postOnTwitter("New posts are available on our Wordpress.\n\nTake a look at ".BLOG_URL." for more info.");
}
    
function postOnTwitter($conteudo){
    $twitterBot = new AmTwitterBot(TWITTER_SECRETS);
    $twitterBot->postStatusesUpdate($conteudo);
}// postOnTwitter

function searchInDb($data, $conditions, $table="", $counting=false, $return=false){
    $dB=new Db(SECRETS['servername'],SECRETS['username'],SECRETS['password']);
    $dB->initDB();
    echo($dB->statistcsSearcher($data, $conditions, $table, $counting, $return)); 

}
function postOnDataBase($posts){
    $dB=new Db(SECRETS['servername'],SECRETS['username'],SECRETS['password']);
    $dB->initDB();
    foreach($posts as $post){
        $dB->input("Post",$post);
    }
}

$r = new Redditer();
$posts = $r->on_subreddit("apexlegends", Category::cHot, Time::tDay, 1)->get_posts();
post_to_wordpress($posts[0]);
