<?php

require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/libs/wordpresser/am_wordpress_tools.php";
require_once __DIR__."/redditer.php";//ESTE É O PROBLEM

//use wordpresser\am_wordpress_tools;

date_default_timezone_set("Europe/Lisbon");

$BLOG = array("blogname"=>"SUPA ARTIGO 33", "user"=>"admin", "pass"=>"1234", "blogxmlrpc"=>"http://localhost:9000/xmlrpc.php");
define ("BLOG_USER", $BLOG['user']);
/*
 * user ou password errada dará
 * @wordpress_postToBlog : Something went wrong - 403 : Incorrect username or password.
 *
 * um post com data futura fica com post_status "future" e só aparecerá quando o futuro chegar...
 */
define ("BLOG_PASS", $BLOG['pass']);
define ("BLOG_XMLRPC", $BLOG['blogxmlrpc']);

function postFromRedditToWordpress($postArray,$allowComments=true,$allowPings=true){
    $post=$postArray[0];
    //foreach($postArray as $post){
    $text=$post->body."<br><p>Author:".$post->author."<p><br><h4>The most Upvoted Comment</h4>"."<br>"."<h4>The most Awarded Comment</h4>"."<br>"."<h4>The most Controversial Comment</h4>";
    $res=wordpress_postToBlog($post->title, $text, "test", "Test",  null, $post->created, $allowComments,$allowPings,BLOG_USER, BLOG_PASS, BLOG_XMLRPC);
    echo ($res);
    //}
}
/*
$ret = wordpress_postToBlog (
    $title = "um post via AM's Wordpress Tools",
    $body = "Funciona?",
    $cats = "test, testing",
    $keywordsString = "wordpresser, bot",
    $featuredImageId=null,
    $dateCreatedInt = mktime(
        $hour=16,
        $minutes=36,
        $seconds=15,
        $month=4,
        $day=27,
        $year=2019
    ),
    //$dateCreatedInt = "2017-05-08", //para a data de agora desde que a timezone esteja correta no Wordpress
    $allowComments=true,
    $allowPings=true,
    $user=BLOG_USER,
    $pass=BLOG_PASS,
    $blogXmlRpcDotPhpFullUrl=BLOG_XMLRPC
);
/*
 * em caso de sucesso
 * @wordpress_postToBlog : Posted OK!
 */


$r = new Redditer("apexlegends", Category::cTop, Time::tDay, false, 10);
$json = $r->get_json();
$postsList = $r->get_posts($json, 1);/*
var_dump($postsList);*/
postFromRedditToWordpress($postsList);
//Houve outro problema, o Thumbnail nao esta a ser aceite pelo wordpress_postToBlog
