<?php
//use am\internet\AmTwitterBot;

require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/libs/wordpresser/am_wordpress_tools.php";
require_once __DIR__."/redditer.php";//ESTE É O PROBLEM
require_once __DIR__."/libs/twitter_bot/AmTwitterBot.php";
require_once "secrets.php";

//use wordpresser\am_wordpress_tools;

date_default_timezone_set("Europe/Lisbon");
define("SECRETS",$SECRETS);
$BLOG = array("blogname"=>"SUPA ARTIGO 33", "user"=>"admin", "pass"=>"1234", "blogxmlrpc"=>"http://localhost:9000/xmlrpc.php");
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
function postFromRedditToWordpress($postArray,$quant=-1,$allowComments=true,$allowPings=true){
    if($quant==-1){
        $quant=count($postArray);
    }
    for($i=0;$i<$quant;$i++){  
       $post=$postArray[$i];
        $text=$post->body."<br><p>Author:".$post->author."<p><br><h4>The most Upvoted Comment</h4>"."<br>"."<h4>The most Awarded Comment</h4>"."<br>"."<h4>The most Controversial Comment</h4>";
        //$res=wordpress_postToBlog($post->title, $text, "test", "Test",  null, $post->created, $allowComments,$allowPings,BLOG_USER, BLOG_PASS, BLOG_XMLRPC);
        //echo $res;    
    }

    postOnTwitter("New posts are available on our Wordpress.\n\nTake a look at the ".BLOG['blogname']." for more info.");
    
}
function postOnTwitter($conteudo){
    $twitterBot = new AmTwitterBot(SECRETS);

    $twitterBot->postStatusesUpdate($conteudo);
    
}
/*
 * em caso de sucesso
 * @wordpress_postToBlog : Posted OK!
 */


$r = new Redditer("apexlegends", Category::cTop, Time::tDay, false, 10);
$json = $r->get_json();
$postsList = $r->get_posts($json);
postFromRedditToWordpress($postsList);
//Houve outro problema, o Thumbnail nao esta a ser aceite pelo wordpress_postToBlog
