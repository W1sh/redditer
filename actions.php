<?php

require_once "redditer.php";
require_once "wordpresser.php";
require_once "utils.php";

$method = $_SERVER['REQUEST_METHOD'];
$path_info = $_SERVER['PATH_INFO'];
$action = explode("/", $path_info)[1];
$bot = new Redditer();

switch ($method) {
    case "POST":
        switch ($action) {
            case "searchSubreddit":
                if ($_REQUEST['nameQuery'] != "") {
                    $array = $bot->on_subreddit(
                        $_REQUEST['nameSubreddit'],
                        $_REQUEST['nameSelectCategory'],
                        $_REQUEST['nameSelectTime'],
                        $_REQUEST['nameLimit']
                    )->search($_REQUEST['nameQuery'])->get_posts();
                } else {
                    $array = $bot->on_subreddit(
                        $_REQUEST['nameSubreddit'],
                        $_REQUEST['nameSelectCategory'],
                        $_REQUEST['nameSelectTime'],
                        $_REQUEST['nameLimit']
                    )->get_posts();
                }
                $stats = get_statistics($array);
                echo json_encode($array);
                echo json_encode($stats);
                break;
            case "searchLink":
                $array = $bot->get_post_from_url($_REQUEST['nameRedditURL']);
                $stats = get_statistics($array);
                echo json_encode($array);
                echo json_encode($stats);
                break;
            case "postToTwitter":
                $title = $_REQUEST['info0'];
                $url = $_REQUEST['info1'];
                postOnTwitter("Just saw - " . $title . " - on reddit. It was sick! \n\nCheck it out on: " . $url);
                break;
            case "postToWordpress":
                $post = $bot->get_post_from_url($_REQUEST['info']);
                echo post_to_wordpress($post);
                break;
        }
        break;
}
