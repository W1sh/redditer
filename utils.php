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
    $arrayMostLiked = array();
    $arrayMostEngaged = array();
    $arrayMostAwarded = array();
    $totalComments = array();
    foreach($mPostsList as $post){
        $totalScore = $totalScore + $post->score;
        $totalNumComments = $totalNumComments + $post->numComments;
        $totalAwards = $totalAwards + $post->awards;
        $totalComments = array_merge($totalComments, $post->comments);
        $postStatistics = engagement_statistics($post->comments);

        $mostLikedAssocArrayKeys = array_keys($postStatistics['most_liked']);
        $mostLikedKey = $mostLikedAssocArrayKeys[0];
        $arrayMostLiked[$mostLikedKey] = $postStatistics['most_liked'][$mostLikedKey];

        $mostEngagedAssocArrayKeys = array_keys($postStatistics['most_engaged']);
        $mostEngagedKey = $mostEngagedAssocArrayKeys[0];
        $arrayMostEngaged[$mostEngagedKey] = $postStatistics['most_engaged'][$mostEngagedKey];

        $mostAwardedAssocArrayKeys = array_keys($postStatistics['most_awarded']);
        $mostAwardedKey = $mostAwardedAssocArrayKeys[0];
        $arrayMostAwarded[$mostAwardedKey] = $postStatistics['most_awarded'][$mostAwardedKey];
    }// foreach
    arsort($arrayMostLiked);
    arsort($arrayMostEngaged);
    arsort($arrayMostAwarded);

    echo count($totalComments).PHP_EOL;
    $totalEngagementStatistics = engagement_statistics($totalComments);

    $mostLikedRedditorMultiple = $totalEngagementStatistics['most_liked'];
    $mostEngagedRedditorMultiple = $totalEngagementStatistics['most_engaged'];
    $mostAwardedRedditorMultiple = $totalEngagementStatistics['most_awarded'];


    $mostLikedRedditorSingle = array_slice($arrayMostLiked, 0, 1);
    $mostEngagedRedditorSingle = array_slice($arrayMostEngaged, 0, 1);
    $mostAwardedRedditorSingle = array_slice($arrayMostAwarded, 0, 1);

    $result = array(
        'subreddit' => $mPostsList[0]->subreddit,
        'num_posts' => count($mPostsList),
        'total_score' => $totalScore,
        'total_num_comments' => $totalNumComments,
        'total_awards' => $totalAwards,
        'most_liked_single' => $mostLikedRedditorSingle,
        'most_liked_multiple' => $mostLikedRedditorMultiple,
        'most_engaged_single' => $mostEngagedRedditorSingle,
        'most_engaged_multiple' => $mostEngagedRedditorMultiple,
        'most_awarded_single' => $mostAwardedRedditorSingle,
        'most_awarded_multiple' => $mostAwardedRedditorMultiple,
        'most_used_words_in_title' => words_frequency_map($mPostsList, "title", 10),
        'most_used_words_in_body' => words_frequency_map($mPostsList, "body", 10),
    );// array
    return $result;
}// get_statistics

function engagement_statistics($pArrayComments) : array{
    $redditors = redditors_frequency_map($pArrayComments);
    $totalAwardsRedditors = array();
    $totalScoreRedditors = array();
    foreach($redditors as $redditor => $frequency){
        $commentsByRedditor = filter_by_redditor($pArrayComments, $redditor);
        $totalAwards = 0;
        $totalScore = 0;
        foreach ($commentsByRedditor as $comment){
            $totalAwards = $totalAwards + $comment->awards;
            $totalScore = $totalScore + $comment->score;
        }// foreach
        $totalAwardsRedditors[$redditor] = $totalAwards;
        $totalScoreRedditors[$redditor] = $totalScore;
    }// foreach
    arsort($totalAwardsRedditors);
    arsort($totalScoreRedditors);
    
    $mostEngaged = array_slice($redditors, 0, 1);
    $mostLiked = array_slice($totalScoreRedditors, 0, 1);
    $mostAwarded = array_slice($totalAwardsRedditors, 0, 1);
    $result = array(
        'most_engaged'=>$mostEngaged,
        'most_liked'=>$mostLiked,
        'most_awarded'=>$mostAwarded,
        //'avg_likes'=>array_slice($totalScoreRedditors, 0, 1)/count($totalScoreRedditors)
    );// array
    return $result;
}// engagement_statistics

function filter_by_redditor($pArrayComments, $pRedditor){
    return array_filter($pArrayComments, function ($item) use ($pRedditor){
        return $item->author == $pRedditor;
    });
}// filter_by_redditor

function redditors_frequency_map($pArrayComments){
    $redditors = array();
    foreach ($pArrayComments as $comment) {
        $redditors[] = $comment->author;
    }// foreach
    $frequencyMap = array_count_values($redditors);
    arsort($frequencyMap);
    return $frequencyMap;
}// redditors_frequency_map

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