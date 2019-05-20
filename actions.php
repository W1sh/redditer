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
                $array = $bot->on_subreddit("apexlegends", Category::cTop, Time::tDay, 2)->get_posts();
                echo json_encode($array);
                break;
        }
    break;
}