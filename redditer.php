<?php
require_once "./vendor/autoload.php";
require_once "structures.php";
require_once "post.php";
require_once "comment.php";
require_once "utils.php";

use am\internet\HttpHelper;

// TODO: implementar mais estatisticas
// average post score
// average post comments
// average title length ?
// average post body length

// TODO: PERGUNTAR:
// ferramenta para visualizar estatisticas GOOGLE PIE CHARTS
// wordpresser nao encontra am_wordpress_tools 

// https://www.reddit.com/r/apexlegends/search/?q=pathfinder&restrict_sr=1&sort=relevance&t=hour&include_over_18=1

class Redditer {

    private $mHttpHelper;
    private $mJURL = false;
    private $mPostsList = array();
    private $mCommentsList = array();
    private $mQuery = array();

    const REDDIT_URL = "https://www.reddit.com/";
    const REDDIT_BASE = self::REDDIT_URL."%s.json?t=%s&limit=%d";
    const REDDIT_SEARCH_BASE = self::REDDIT_URL."search.json?q=%s&type=%s&limit=%d&sort=%s&t=%s&include_over_18=%d";
    const SUBREDDIT_BASE = self::REDDIT_URL."r/%s/%s.json?t=%s&limit=%d";
    const SUBREDDIT_SEARCH_BASE = self::REDDIT_URL."r/%s/search.json?restrict_sr=1&q=%s&sort=%s&t=%s&include_over_18=%d&limit=%d";

    public function __construct(){
        $this->mHttpHelper = new HttpHelper("Redditer v1.0");
    }// __construct

    public function on_reddit($pTime=Time::tDay, $pLimit=100){
        $this->mPostsList = array();
        $this->mCommentsList = array();
        $this->mQuery['subreddit'] = false;
        $this->mQuery['after'] = false;
        $this->mQuery['time'] = $pTime;
        $this->mQuery['limit'] = $pLimit;
        return $this;
    }

    public function on_subreddit($pSubreddit="movies", $pCategory=Category::cHot, $pTime=Time::tDay, $pLimit=100){
        $this->mPostsList = array();
        $this->mCommentsList = array();
        $this->mQuery['subreddit'] = $pSubreddit;
        $this->mQuery['category'] = $pCategory;
        $this->mQuery['time'] = $pTime;
        $this->mQuery['limit'] = $pLimit;
        $this->mQuery['after'] = false;
        return $this;
    }// on_subreddit

    public function search($pInput, $pSort="relevance", $pTime=Time::tDay, $pOver18=false){
        $this->mQuery['input'] = $pInput;
        $this->mQuery['sort'] = $pSort;
        $this->mQuery['time'] = $pTime;
        $this->mQuery['over18'] = $pOver18;
        $this->mQuery['type'] = "link";
        if($this->mQuery['subreddit'] != false){
            $this->subreddit_build_search_query();
        }
        $this->reddit_build_search_query();
        return $this;
    }// search

    public function get_post_from_url($pUrl){
        $this->evaluate_url($pUrl);
        $this->get_posts(true);
        return $this->mPostsList[0];
    }

    private function evaluate_url($pUrl){
        $bIsPost = strpos($pUrl, "comments");
        if($bIsPost){
            $this->mJURL = substr($pUrl, 0, -1).".json";
        }else{
            //echo "Invalid URL";
        }
    }

    public function get_posts($pIsSinglePost=false){
        if($this->mJURL == false){
            if($this->mQuery['subreddit'] != false){
                $this->subreddit_build_query();
            }else{
                $this->reddit_build_query();
            }
        }
        echo $this->mJURL.PHP_EOL;
        $json = $this->get_json();
        if($pIsSinglePost){
            $posts = $json[0]->data->children;
            $after = null;
        }else{
            $posts = $json->data->children;
            $after = $json->data->after;
        }
        $postsCreated = 0;
        foreach ($posts as $post){
            $redditPost = Post::newPostData($post->data);
            $url = substr($redditPost->postUrl, 0, -1).".json";
            echo $url.PHP_EOL;
            $json = $this->get_json($url);
            if($json != false){
                $postsCreated = $postsCreated + 1;
                $jcomments = $json[1]->data->children;
                $this->extract_comments($jcomments);
                $redditPost->set_comments($this->mCommentsList);
                $this->mCommentsList = array(); // need to clear due to performance issues
                $this->mPostsList[] = $redditPost;
            }else{
                //echo "Failed to retrieve json from url: ".$url.PHP_EOL;
            }// if
        }// foreach
        if($postsCreated < $this->mQuery['limit']){
            $this->mQuery['limit'] = $this->mQuery['limit'] - $postsCreated;
            if($after != null){
                $this->mQuery['after'] = $after;
                if($this->mQuery['subreddit'] != false){
                    $this->subreddit_build_query();
                }else{
                    $this->reddit_build_query();
                }
                $this->get_posts();
            }//if
        }// if
        return $this->mPostsList;
    }// get_posts

    private function extract_comments($pJsonComments) {
        foreach ($pJsonComments as $comment){
            if($comment->kind == "t1"){
                $jReplies = $comment->data->replies != "" ? $comment->data->replies->data->children : null;
                if(is_array($jReplies) && count($jReplies)>0){
                    $jNumReplies = count($comment->data->replies->data->children);
                    $this->extract_comments($jReplies);
                }else{
                    $jNumReplies = 0;
                }// if
                $this->mCommentsList[] = Comment::newCommentData($comment->data, $jNumReplies);
            }else if($comment->kind == "more"){
                // comentado devido aos problemas de tempo, para cada comentario escondido
                // é necessário abrir uma pagina e extrair o comentário o que torna o processo
                // de extrair um comentario incrivelmente lento - 01-07-2019
                /*$jHiddenComments = $comment->data->children;
                foreach($jHiddenComments as $commentTag){
                    $url = str_replace(".json", "/".$commentTag.".json", $this->mJURL);
                    $json = $this->get_json($url);
                    if($json != false){
                        $jcomments = $json[1]->data->children;
                        $this->extract_comments($jcomments);
                    }// if
                }// foreach*/
            }// if
        }// foreach
    }// extract_comment

    private function reddit_build_search_query(){
        $query = $this->mQuery;
        $url = sprintf(self::REDDIT_SEARCH_BASE, $query['input'], $query['type'],
            $query['limit'], $query['sort'], $query['time'], $query['over18']);
        $this->mJURL = $query['after'] == false ? $url : $url."&after=".$query['after'];
    }

    private function reddit_build_query(){
        $query = $this->mQuery;
        $url = sprintf(self::REDDIT_BASE, $query['category'] ?? "best",
            $query['time'], $query['limit']);
        $this->mJURL = $query['after'] == false ? $url : $url."&after=".$query['after'];
    }

    private function subreddit_build_search_query(){
        $query = $this->mQuery;
        $url = sprintf(self::SUBREDDIT_SEARCH_BASE, $query['subreddit'], $query['input'],
            $query['sort'], $query['time'], $query['over18'], $query['limit']);
        $this->mJURL = $query['after'] == false ? $url : $url."&after=".$query['after'];
    }// subreddit_build_search_query

    private function subreddit_build_query(){
        $query = $this->mQuery;
        $url = sprintf(self::SUBREDDIT_BASE, $query['subreddit'],
            $query['category'], $query['time'], $query['limit']);
        $this->mJURL = $query['after'] == false ? $url : $url."&after=".$query['after'];
    }// subreddit_build_query

    private function get_json($pUrl=null){
        $url = $pUrl ?? $this->mJURL;
        //echo $url.PHP_EOL;
        $result = $this->mHttpHelper->http($url);
        $data = $result[HttpHelper::KEY_BIN];
        $status = $result[HttpHelper::KEY_STATUS];
        if($status['http_code'] != 200){
            return false;
        }// if
        return json_decode($data);
    }// get_json
}// Redditer