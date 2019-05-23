<?php
use function Rap2hpoutre\RemoveStopWords\remove_stop_words;
use am\internet\HttpHelper;

function words_frequency_map($pList, $pParam, $pMapSize){
    $aStrings = array();
    foreach ($pList as $element) {
        $string = remove_stop_words($pParam != null ? $element->$pParam : $element);
        $stringAsArray = str_word_count($string, 1);
        $aStrings = array_merge($aStrings, $stringAsArray);
    }// foreach
    $frequencyMap = array_count_values($aStrings);
    arsort($frequencyMap);
    return array_slice($frequencyMap, 0, $pMapSize);    
}// words_frequency_map

function get_statistics($mPostsList) : array{
    if(count($mPostsList) == 0){
        return "Failed to evaluate posts. No posts were found.";
    }// if
    $totalScore = 0;
    $totalNumComments = 0;
    $totalAwards = 0;
    foreach($mPostsList as $post){
        $totalScore = $totalScore + $post->score;
        $totalNumComments = $totalNumComments + $post->numComments;
        $totalAwards = $totalAwards + $post->awards;
    }// foreach
    $result = array(
        'subreddit' => $mPostsList[0]->subreddit,
        'num_posts' => count($mPostsList),
        'total_score' => $totalScore,
        'total_num_comments' => $totalNumComments,
        'total_awards' => $totalAwards,
        'most_used_words_in_title' => words_frequency_map($mPostsList, "title", 10),
        'most_used_words_in_body' => words_frequency_map($mPostsList, "body", 10),
    );// array
    return $result;
}// get_statistics

function time_as_pretty_string($postedDate) : string{
    $seconds = time() - $postedDate;
    if($seconds > 86400){
        $days = ceil((($seconds / 60)/60)/24);
        return $days." days ago";
    }else if($seconds > 3600){
        $hours = ceil(($seconds / 60)/60);
        return $hours." hours ago";
    }else if($seconds > 60){
        $minutes = ceil($seconds / 60);
        return $minutes." minutes ago";
    }
    return $seconds." seconds ago";
}// time_as_pretty_string

function build_time($unixTimestamp){
    $time = gmdate("Y-m-d-G-i-s", $unixTimestamp);
    $parts = explode("-", $time);
    $partYear = intval ($parts[0]);
    $partMonth=intval ($parts[1]);
    $partDay=intval ($parts[2]);
    $partHours=intval ($parts[3]);
    $partMinutes=intval ($parts[4]);
    $partSeconds=intval ($parts[5]);
    return mktime($partHours, $partMinutes, $partSeconds, $partMonth, $partDay, $partYear);
}// build_time

function download_thumbnail($pUrl, $pFileName){
    $helper = new HttpHelper();
    $ret = $helper->simpleDownloader($pUrl, $pFileName, HttpHelper::DEFAULT_REFERRER, HttpHelper::METHOD_GET);
    return $ret['filename'];
}