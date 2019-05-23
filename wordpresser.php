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
define("BLOG",$BLOG);
/*
 * user ou password errada dará
 * @wordpress_postToBlog : Something went wrong - 403 : Incorrect username or password.
 *
 * um post com data futura fica com post_status "future" e só aparecerá quando o futuro chegar...
 */
define ("BLOG_PASS", $BLOG['pass']);
define ("BLOG_XMLRPC", $BLOG['blogxmlrpc']);

function post_multiple_to_wordpress($posts,$allowComments=true,$allowPings=true){
    foreach($posts as $post){
        post_to_wordpress($post, $allowComments, $allowPings);
    }
}

function post_to_wordpress($post,$allowComments=true,$allowPings=true) {
    $helper = new HttpHelper();
    $bReachableUrl = $helper->isUrlReachable($post->thumbnail);
    if($bReachableUrl){
        $filename = download_thumbnail($post->thumbnail, "temp001");
        $data = wordpress_uploadBinary(__DIR__."/".$filename,BLOG_USER,BLOG_PASS,BLOG_XMLRPC);
        $ret = wordpress_postToBlog (
            $post->title,
            $post->__toString(),
            array("Test","Testa"),//$categorias not working
            $keywordsString = "wordpresser, redditer, bot",
            $featuredImageId=$data['id'],
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
/*
 * em caso de sucesso
 * @wordpress_postToBlog : Posted OK!
 */


$r = new Redditer();
$array = $r->on_subreddit("apexlegends", Category::cTop, Time::tDay, 10)->get_posts();
post_to_wordpress($array[0], 1);
//Houve outro problema, o Thumbnail nao esta a ser aceite pelo wordpress_postToBlog
