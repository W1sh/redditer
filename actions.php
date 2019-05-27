<?php

require_once "redditer.php";

$method = $_SERVER['REQUEST_METHOD'];
$path_info = $_SERVER['PATH_INFO'];
$action = explode("/", $path_info)[1];
$bot = new Redditer();

switch ($method){
    case "POST":
        switch($action){
            case "searchSubreddit":
                $array = $bot->on_subreddit($_REQUEST['Subreddit'], $_REQUEST['nameSelectCategory'], $_REQUEST['nameSelectTime'], 2)->get_posts();
                //var_dump($array);
                echo json_encode($array);
                break;
            case "searchLink":
                echo $_REQUEST['nameRedditURL'];
                $array = $bot->get_post_from_url($_REQUEST['nameRedditURL']);
                //var_dump($array);
                echo json_encode($array);
                break;
        }
    break;
}