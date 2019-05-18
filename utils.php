<?php
use function Rap2hpoutre\RemoveStopWords\remove_stop_words;

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