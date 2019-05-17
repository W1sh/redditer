<?php
use function Rap2hpoutre\RemoveStopWords\remove_stop_words;

function words_frequency_map($pList, $pParam, $pMapSize){
    $aStrings = array();
    foreach ($pList as $element) {
        $string = remove_stop_words($pParam != null ? $element->$pParam : $element);
        $stringAsArray = str_word_count($string, 1);
        $aStrings = array_merge($aStrings, $stringAsArray);
    }
    $frequencyMap = array_count_values($aStrings);
    arsort($frequencyMap);
    return array_slice($frequencyMap, 0, $pMapSize);    
}// words_frequency_map