<?php
/**
 * Artur Marques
 */
require_once "./vendor/autoload.php";

use wordpresser\am_wordpress_tools;

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
function postFromRedditToWordpress(
$title, 
$body, 
$cats, 
$keyWordString, 
$featuredImageId=null, 
$postDate,
$allowComments=true,
$allowPings=true,
$user=BLOG_USER,
$pass=BLOG_PASS,
$blogXmlRpcDotPhpFullUrl=BLOG_XMLRPC ){

    $res=wordpress_postToBlog($title, $body, $cats, $keyWordString,  $featuredImageId, $postDate,$allowComments,$allowPings,$user, $pass, $blogXmlRpcDotPhpFullUrl);
    echo ($res);
    
}



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

var_dump ($ret);