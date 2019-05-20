<?php

abstract class Category{
    const cHot = "hot";
    const cNew = "new";
    const cControversial = "controversial";
    const cTop = "top";
    const cRising = "rising";
}// Category

abstract class Time{
    const tHour = "hour";
    const tDay = "day";
    const tWeek = "week";
    const tMonth = "month";
    const tYear = "year";
    const tAll = "all";
}// Time

/*abstract class Type{
    const user = "user";
    const subreddit = "sr%2Cuser";
    const post = "link";
}*/