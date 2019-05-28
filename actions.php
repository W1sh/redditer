<?php

require_once "redditer.php";
require_once "wordpresser.php";

$method = $_SERVER['REQUEST_METHOD'];
$path_info = $_SERVER['PATH_INFO'];
$action = explode("/", $path_info)[1];
$bot = new Redditer();

switch ($method){
    case "POST":
        switch($action){
            case "searchSubreddit":
                if($_REQUEST['nameQuery'] != ""){
                    $array = $bot->on_subreddit($_REQUEST['nameSubreddit'], $_REQUEST['nameSelectCategory'],
                        $_REQUEST['nameSelectTime'], $_REQUEST['nameLimit'])->search($_REQUEST['nameQuery'])->get_posts();
                }else{
                    $array = $bot->on_subreddit($_REQUEST['nameSubreddit'], $_REQUEST['nameSelectCategory'],
                        $_REQUEST['nameSelectTime'], $_REQUEST['nameLimit'])->get_posts();
                }
                //var_dump($array);
                echo json_encode($array);
                break;
            case "searchLink":
                $array = $bot->get_post_from_url($_REQUEST['nameRedditURL']);
                //var_dump($array);
                echo json_encode($array);
                break;
            case "postToTwitter":
                $post = $bot->get_post_from_url($_REQUEST['purl']);
                echo post_to_wordpress($post);
                break;
            case "postToWordpress":
                echo $_REQUEST['purl'];
                break;
        }
    break;
}