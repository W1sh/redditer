<?php
use am\internet\HttpHelper;

//use am\internet\AmTwitterBot;

require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/libs/wordpresser/am_wordpress_tools.php";
require_once __DIR__."/redditer.php";
require_once __DIR__."/Db.php";
require_once __DIR__."/libs/twitter_bot/AmTwitterBot.php";
require_once "secrets.php";

date_default_timezone_set("Europe/Lisbon");

define ("BLOG_USER", $WORDPRESS_SECRETS['user']);
define ("BLOG_PASS", $WORDPRESS_SECRETS['password']);
define ("BLOG_XMLRPC", $WORDPRESS_SECRETS['url']."xmlrpc.php");


function post_multiple_to_wordpress($posts,$allowComments=true,$allowPings=true){
    foreach($posts as $post){
        echo $post->contentUrl['url'];
        post_to_wordpress($post, $allowComments, $allowPings);
    }
}// post_multiple_to_wordpress

function post_to_wordpress($post,$allowComments=true,$allowPings=true) {
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
    //postOnTwitter("New posts are available on our Wordpress.\n\nTake a look at the ".BLOG['blogname']." for more info.");
}
    
function postOnTwitter($conteudo){
    //$twitterBot = new AmTwitterBot(SECRETS);
    //$twitterBot->postStatusesUpdate($conteudo);
}// postOnTwitter

function searchInDb($data, $conditions, $table="", $counting=false, $return=false){
    $dB=new Db(SECRETS['servername'],SECRETS['username'],SECRETS['password']);
    $dB->initDB();
    var_dump($dB->statistcsSearcher($data, $conditions, $table, $counting, $return)); 

}
function postOnDataBase($posts){
    $dB=new Db(SECRETS['servername'],SECRETS['username'],SECRETS['password']);
    $dB->initDB();
    foreach($posts as $post){
        $dB->input("Post",$post);
    }
}
/*
$r = new Redditer();
$array = $r->on_subreddit("AskReddit", Category::cTop, Time::tDay, 1)->get_posts();
//post_multiple_to_wordpress($array);
/*$post = $r->get_post_from_url("https://www.reddit.com/r/factorio/comments/bsf9lh/factorio_is_everywhere_and_its_outstanding/");
post_to_wordpress($post);
postOnDataBase($array);
searchInDb("*",array(),"", true, false);*/
//var_dump($post);