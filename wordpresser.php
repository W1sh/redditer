<?php
use am\internet\HttpHelper;

//use am\internet\AmTwitterBot;

require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/libs/wordpresser/am_wordpress_tools.php";
require_once __DIR__."/redditer.php";
require_once __DIR__."/Db.php";
require_once __DIR__."/libs/twitter_bot/AmTwitterBot.php";
require_once "secrets.php";
require_once "utils.php";

date_default_timezone_set("Europe/Lisbon");

define ("BLOG_USER", $WORDPRESS_SECRETS['user']);
define ("BLOG_PASS", $WORDPRESS_SECRETS['password']);
define ("BLOG_XMLRPC", $WORDPRESS_SECRETS['url']."xmlrpc.php");

function post_multiple_to_wordpress($posts,$allowComments=true,$allowPings=true){
    foreach($posts as $post){
        post_to_wordpress($post, $allowComments, $allowPings);
    }
}// post_multiple_to_wordpress

function post_to_wordpress($post,$allowComments=true,$allowPings=true) {
    $helper = new HttpHelper();
    if($post->contentUrl['is_video'] == false){
        $bReachableUrl = $helper->isUrlReachable($post->contentUrl['url']);
    }
    if($bReachableUrl){
        $filename = download_thumbnail($post->contentUrl['url'], "temp001");
        $data = wordpress_uploadBinary(__DIR__."/".$filename,BLOG_USER,BLOG_PASS,BLOG_XMLRPC);
        $post->contentUrl['image_id'] = $data['id'];
        unlink(__DIR__."/".$filename);// destroy temp image file
    }
    wordpress_postToBlog (
        $post->title,    // title
        $post->__toString(),    // body
        array("reddit", substr($post->subreddit, 2)),    // categorias
        "wordpresser, redditer, bot",    // keywords
        $post->contentUrl['is_video'] == false ? $post->contentUrl['image_id'] : null,    // featuredImageId
        build_time($post->created),    // date_created
        $allowComments,
        $allowPings,
        BLOG_USER,    // user
        BLOG_PASS,    // pass
        BLOG_XMLRPC);    // xmlrpc 
    //postOnTwitter("New posts are available on our Wordpress.\n\nTake a look at the ".BLOG['blogname']." for more info.");
}
    
function postOnTwitter($conteudo){
    //$twitterBot = new AmTwitterBot(SECRETS);
    //$twitterBot->postStatusesUpdate($conteudo);
}// postOnTwitter
function turnArrayIntoObject($array,$type)
{
    $res=array();
    switch($type){
        case "Posts":
            foreach($array as $object){
                $object['comments']=searchInDb(array("PostId='".$object['PostId']."'"),false,true,"Comments");  
                $res[]=Post::newPostArray($object);
                echo"CREATE POSTS: ";
                var_dump($object).PHP_EOL;
            }
        break;
        case "Comments":
            foreach($array as $object){
                $res[]=Comment::newCommentArray($object);
            }
        break;
    }
    return $res;
}
function searchInDb($conditions=array(), $counting=false, $return=false,$table="",$data=array("*")){
    $dB=new Db("localhost","root","");
    $dB->initDB();
    $res=array();
    $count=0;
    if($table==""){
        $res['Posts']=$dB->statistcsSearcher($data, $conditions, "Posts", $counting, $return);
        $res['Comments']=$dB->statistcsSearcher($data, $conditions, "Comments", $counting, $return);
        if($counting&&$return||!$counting&&!$return){
            $count=$res['Posts']['Count']+$res['Comments']['Count'];
            $res['Posts']=turnArrayIntoObject($res['Posts']['Result']['object'], "Posts");
            $res['Comments']=turnArrayIntoObject($res['Comments']['Result']['object'], "Comments");
            return array('Result'=>$res,'Count'=>$count);
        }elseif ($counting&&!$return) {
            $count=$res['Posts']['Count']+$res['Comments']['Count'];
            return $count;
        }else{
            $res['Posts']=turnArrayIntoObject($res['Posts']['Result']['object'], "Posts");
            $res['Comments']=turnArrayIntoObject($res['Comments']['Result']['object'], "Comments");
            return $res;
        }
    }else{
        $res=$dB->statistcsSearcher($data, $conditions, $table, $counting, $return);   
        if($counting&&$return||!$counting&&!$return){
            return array('Result'=>turnArrayIntoObject($res['Result'], $table), 'Count'=> $res['Count']);
        }elseif ($counting&&!$return) {
            $count=$res;          
            return $count;
        }else{
            $res=turnArrayIntoObject($res['object'], $table);
            return $res;
        }
}
}
function postOnDataBase($posts){
    $dB=new Db("localhost","root","");
    $dB->initDB();
    foreach($posts as $post){
        $dB->input("Post",$post);
    }
}

$r = new Redditer();/*
$posts = $r->on_subreddit("apexlegends", Category::cHot, Time::tDay, 1)->get_posts();*/
$post=$r->get_post_from_url("https://www.reddit.com/r/Documentaries/comments/bur9if/children_of_the_stars_2012_is_a_documentary_about/");
//postOnDataBase(array($post));
//var_dump(searchInDb(array(),true, true));*/
print_r(searchInDb(array(),true,false,"Posts"));
echo "FINITO";
/*$stats = get_statistics($posts);
print_r($stats);*/