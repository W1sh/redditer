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
    $bReachableUrl = $helper->isUrlReachable($post->contentUrl);
    echo PHP_EOL.$post->postUrl.PHP_EOL;
    if($bReachableUrl){
        $filename = download_thumbnail($post->contentUrl, "temp001");
        $data = wordpress_uploadBinary(__DIR__."/".$filename,BLOG_USER,BLOG_PASS,BLOG_XMLRPC);
        echo PHP_EOL."DATA".PHP_EOL;
        print_r($data);
        echo PHP_EOL."DATA".PHP_EOL;
        $ret = wordpress_postToBlog (
            $post->title,
            "<video width=\"100%\" height=\"auto\" controls><source src=".$data['url']." type=\"video/mp4\"></video>".$post->__toString(),
            array("Test","Testa"),//$categorias not working
            $keywordsString = "wordpresser, redditer, bot",
            $featuredImageId=null,
            build_time($post->created),
            $allowComments,
            $allowPings,
            $user=BLOG_USER,
            $pass=BLOG_PASS,
            $blogXmlRpcDotPhpFullUrl=BLOG_XMLRPC);           
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
