<?php
use am\internet\HttpHelper;

//use am\internet\AmTwitterBot;

require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/libs/wordpresser/am_wordpress_tools.php";
require_once __DIR__."/redditer.php";
require_once __DIR__."/libs/twitter_bot/AmTwitterBot.php";
//require_once "secrets.php";

//use wordpresser\am_wordpress_tools;

date_default_timezone_set("Europe/Lisbon");

//define("SECRETS",$SECRETS);
//$BLOG = array("blogname"=>"Test", "user"=>"bot", "pass"=>"1234", "blogxmlrpc"=>"http://localhost/work/dai/wordpress/xmlrpc.php");
$BLOG = array("blogname"=>"Test", "user"=>"admin", "pass"=>"1234", "blogxmlrpc"=>"http://localhost:9000/xmlrpc.php");

define ("BLOG_USER", $BLOG['user']);
define ("BLOG_PASS", $BLOG['pass']);
define ("BLOG_XMLRPC", $BLOG['blogxmlrpc']);

function post_multiple_to_wordpress($posts,$allowComments=true,$allowPings=true){
    foreach($posts as $post){
        post_to_wordpress($post, $allowComments, $allowPings);
    }
}

function post_to_wordpress($post,$allowComments=true,$allowPings=true) {
    $helper = new HttpHelper();
    $bReachableUrl = $helper->isUrlReachable($post->contentUrl['url']);
    if($bReachableUrl){
        $filename = download_thumbnail($post->contentUrl['url'], "temp001");
        $data = wordpress_uploadBinary(__DIR__."/".$filename,BLOG_USER,BLOG_PASS,BLOG_XMLRPC);
        $bHasVideo = strcasecmp($data['type'], "video/mp4")===0;
        if($bHasVideo){
            $post->contentUrl['url'] = $data['url'];
        }
        wordpress_postToBlog (
            $post->title,    // title
            $post->__toString(),    // body
            array("Test","Testa"),    // categorias (not working)
            "wordpresser, redditer, bot",    // keywords
            $post->contentUrl['is_video'] == false ? $post->contentUrl['url'] : null,    // featuredImageId
            build_time($post->created),    // date_created
            $allowComments,
            $allowPings,
            BLOG_USER,    // user
            BLOG_PASS,    // pass
            BLOG_XMLRPC);    // xmlrpc 
    }
    unlink(__DIR__."/".$filename);// destroy temp image file
    //postOnTwitter("New posts are available on our Wordpress.\n\nTake a look at the ".BLOG['blogname']." for more info.");
}
function postOnTwitter($conteudo){
    //$twitterBot = new AmTwitterBot(SECRETS);

    //$twitterBot->postStatusesUpdate($conteudo);
    
}

$r = new Redditer();
$array = $r->on_subreddit("apexlegends", Category::cTop, Time::tDay, 10)->get_posts();
post_to_wordpress($array[3]);
