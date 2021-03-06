window.onload = boot;

var ID_URL_FORM = "idUrlForm",
    ID_PARAMETERS_FORM = "idParametersForm",
    ID_URL="idInputUrl",
    ID_QUERY = "idInputQuery",
    ID_INPUT_SUBREDDIT = "idInputSubreddit",
    ID_SELECT_CATEGORY = "idSelectCategory",
    ID_SELECT_TIME = "idSelectTime",
    ID_LIMIT = "idInputLimit",
    ID_BTN_PARAMETERS = "idBtnParameters",
    ID_SHOWCASE_CONTAINER = "idShowcaseContainer",
    ID_STATISTICS_CONTAINER = "idStatisticsContainer",
    ID_RIGHT_CHART = "idRightChart",
    ID_LEFT_CHART = "idLeftChart",
    ID_RIGHT_CHART_CONTAINER = "idRightChartContainer",
    ID_LEFT_CHART_CONTAINER = "idLeftChartContainer";

var eleUrlForm, eleUrl, eleParametersForm, eleInputSubreddit, eleSelectCategory, eleSelectTime,
    eleLimit, eleQuery, eleBtnParamters, eleShowcaseContainer, eleStatisticsContainer, eleRightChart,
    eleLeftChart, eleRightChartContainer, eleLeftChartContainer;
var rightChartCtx, leftChartCtx;
var URL_DO_SERVICO = "actions.php";

function $(pId) {
    return document.getElementById(pId);
}// $

function allOk(pObjects) {
    for (var object of pObjects) {
        if (object == null) return false;
    }
    return true;
} //allNotNull

function boot() {
    eleUrlForm = $(ID_URL_FORM);
    eleParametersForm = $(ID_PARAMETERS_FORM);
    eleUrl = $(ID_URL);
    eleInputSubreddit = $(ID_INPUT_SUBREDDIT);
    eleSelectCategory = $(ID_SELECT_CATEGORY);
    eleSelectTime = $(ID_SELECT_TIME);
    eleQuery = $(ID_QUERY);
    eleLimit = $(ID_LIMIT);
    eleBtnParamters = $(ID_BTN_PARAMETERS);
    eleShowcaseContainer = $(ID_SHOWCASE_CONTAINER);
    eleStatisticsContainer = $(ID_STATISTICS_CONTAINER);
    eleRightChart = $(ID_RIGHT_CHART);
    eleLeftChart = $(ID_LEFT_CHART);
    eleRightChartContainer = $(ID_RIGHT_CHART_CONTAINER);
    eleLeftChartContainer = $(ID_LEFT_CHART_CONTAINER);

    var objects = [eleUrlForm, eleUrl, eleInputSubreddit, eleParametersForm, eleSelectCategory,
        eleSelectTime, eleQuery, eleLimit, eleBtnParamters, eleShowcaseContainer, eleStatisticsContainer,
        eleRightChart, eleLeftChart, eleRightChartContainer, eleLeftChartContainer];
    var bAllOk = allOk(objects);
    if (!bAllOk) {
        alert("There is 1+ object(s) with a problem.");
        return;
    }// if
    
    rightChartCtx = eleRightChart.getContext('2d');
    leftChartCtx = eleLeftChart.getContext('2d');

    eleParametersForm.onsubmit = sendParametersRequest;
    eleUrlForm.onsubmit = sendURLRequest;
}// boot

function sendURLRequest(){
    clearResults();
    ajax("POST", URL_DO_SERVICO + "/searchLink", eleUrlForm, function () {
        var start = this.responseText.indexOf("[");
        var end = this.responseText.lastIndexOf("]");
        var elementsJson = this.responseText.substr(start, (end - start + 1));
        var statisticsJson = this.responseText.substr(end + 1);
        var elementsArray = JSON.parse(elementsJson);
        var statistics = JSON.parse(statisticsJson);
        for (var element of elementsArray){
            var title = createTitleWithHyperlink(element.title, element.postUrl);
            var info = createInfoSectionString(element.author, element.subreddit, element.timePassed);
            createShowcaseElement(title, info, element.body, element.contentUrl['is_video'], element.contentUrl['url']);
        }
        createStatisticsSection(statistics);
        changeBtnState("success");
        eleShowcaseContainer.scrollIntoView({block: 'start', behavior: 'smooth'});
    });
    changeBtnState("searching");
    return false;
}

function sendParametersRequest(){
    clearResults();
    ajax("POST", URL_DO_SERVICO + "/searchSubreddit", eleParametersForm, function () {
        var start = this.responseText.indexOf("[");
        var end = this.responseText.lastIndexOf("]");
        var elementsJson = this.responseText.substr(start, (end - start + 1));
        var statisticsJson = this.responseText.substr(end + 1);
        var elementsArray = JSON.parse(elementsJson);
        var statistics = JSON.parse(statisticsJson);
        for (var element of elementsArray){
            var title = createTitleWithHyperlink(element.title, element.postUrl);
            var info = createInfoSectionString(element.author, element.subreddit, element.timePassed);
            createShowcaseElement(title, info, element.body, element.contentUrl['is_video'], element.contentUrl['url']);
        }
        createStatisticsSection(statistics);
        changeBtnState("success");
        eleShowcaseContainer.scrollIntoView({block: 'start', behavior: 'smooth'});
    });
    changeBtnState("searching");
    return false;
}

function ajax(pType, pPostUrl, pObject, pFunction) {
    if (pType == "POST" || pType == "GET") {
        var oReq = new XMLHttpRequest();
        if (oReq) {
            oReq.onload = pFunction;
            oReq.open(pType, pPostUrl, true);    
            var bIsForm = typeof pObject === "object" && pObject.nodeName==="FORM";
            if (bIsForm){
                var formData = new FormData(pObject);
                oReq.send(formData);
            }else{
                oReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                if(Array.isArray(pObject)){
                    var stringForUrl = "";
                    for (let i=0; i<pObject.length; i++){
                        if(i != 0 ){
                            stringForUrl +=  "&info" + i + "=" + pObject[i];
                        }else{
                            stringForUrl +=  "info" + i + "=" + pObject[i];
                        }
                    }
                    oReq.send(stringForUrl);
                }else{
                    oReq.send("info=" + pObject);
                }
            }
        } // if
    }else{
        alert("Ajax request type not supported. Must be either GET or POST.");
        return false;
    }// if
} // ajaxGet

function changeBtnState(pState){
    switch(pState){
        case "searching" : 
            eleBtnParamters.className = "btn btn-block btn-lg btn-success";
            eleBtnParamters.innerHTML = "Searching...";
            break;
        case "success" :
            eleBtnParamters.className = "btn btn-block btn-lg btn-primary";
            eleBtnParamters.innerHTML = "Search";
            break;
        default: break;
    }
}

function createShowcaseElement(pTitle, pInfo, pBody, pHasVideo, pUrl){
    var row = document.createElement('div');
    row.className = "row no-gutters";
    var content = document.createElement('div');
    content.className = "col-lg-12 order-lg-1 my-auto showcase-text";
    var title = document.createElement('h2');
    title.innerHTML = pTitle;
    var info = document.createElement('p');
    info.innerHTML = pInfo;
    var body = document.createElement('p');
    body.className = "lead mb-0";
    body.textContent = pBody;

    content.appendChild(title);
    content.appendChild(info);
    content.appendChild(body);
    content.appendChild(document.createElement('br'));

    if(pHasVideo){
        var bodyVideoContainer = document.createElement('div');
        bodyVideoContainer.className= "embed-responsive embed-responsive-16by9";
        var bodyVideo = document.createElement('video');
        bodyVideo.className= "embed-responsive-item";
        bodyVideo.src = pUrl;
        bodyVideo.setAttribute('controls', 'controls');
        bodyVideo.autoplay = false;
        bodyVideoContainer.appendChild(bodyVideo);
        content.appendChild(bodyVideoContainer);
        content.appendChild(document.createElement('br'));
    }
    if(pHasVideo == false && pUrl != false){
        var bodyImage = document.createElement('img');
        bodyImage.className = "img-fluid";
        bodyImage.src = pUrl;
        content.appendChild(bodyImage);
        content.appendChild(document.createElement('br'));
    }
   
    var wordpressButton = document.createElement('button');
    wordpressButton.className = "btn btn-info mr-2";
    wordpressButton.textContent = "Wordpress ";
    wordpressButton.onclick = function(){
        var postUrl = this.parentElement.childNodes[0].firstChild.href;
        wordpressButton.className = "btn btn-success mr-2";
        wordpressButton.textContent = "Posting... ";
        ajax("POST", URL_DO_SERVICO + "/postToWordpress", postUrl, function () {
            wordpressButton.className = "btn btn-success mr-2";
            wordpressButton.textContent = "Posted ";
            console.log(this.responseText);
        });
    };
    var wordpressIcon = document.createElement('i');
    wordpressIcon.className = "fa fa-wordpress";
    wordpressButton.appendChild(wordpressIcon);
    content.appendChild(wordpressButton); 
    var twitterButton = document.createElement('button');
    twitterButton.className = "btn btn-info";
    twitterButton.textContent = "Twitter ";
    twitterButton.onclick = function(){
        var postUrl = this.parentElement.childNodes[0].firstChild.href;
        twitterButton.className = "btn btn-success";
        twitterButton.textContent = "Posting... ";
        var titleWithEndTag = pTitle.substr(pTitle.indexOf(">") + 1);
        var title = titleWithEndTag.slice(0, -4);
        var infoToSend = [title, postUrl];
        ajax("POST", URL_DO_SERVICO + "/postToTwitter", infoToSend, function () {
            twitterButton.className = "btn btn-success";
            twitterButton.textContent = "Posted ";
            console.log(this.responseText);
        });
    };
    var twitterIcon = document.createElement('i');
    twitterIcon.className = "fa fa-twitter";
    twitterButton.appendChild(twitterIcon);
    content.appendChild(twitterButton);
    row.appendChild(content);
    eleShowcaseContainer.appendChild(row);
}

function createInfoSectionString(pAuthor, pSubreddit, pTimePassed){
    var string = "Posted by <strong>" + pAuthor + "</strong> on " + pSubreddit + " - " + pTimePassed;
    return string;
}

function createTitleWithHyperlink(pTitle, pUrl){
    var string = "<a href=\"" + pUrl + "\">" + pTitle + "</a>";
    return string;
}

function createStatisticsSection(pStatistics){
    var h2title = document.createElement('h2');
    h2title.textContent = "Got " + pStatistics.num_posts + " posts from " + pStatistics.subreddit;
    h2title.className = "mb-5";
    // --- FIRST ROW ---
    var firstRow = document.createElement('div');
    firstRow.className = "row";

    // --- FIRST COLUMN ---
    var firstRowFirstColumn = document.createElement('div');
    firstRowFirstColumn.className = "col-lg-4";
    var firstRowFirstColumnContent = document.createElement('div');
    firstRowFirstColumnContent.className = "features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3";
    var firstRowFirstColumnIconContainer = document.createElement('div');
    firstRowFirstColumnIconContainer.className = "features-icons-icon d-flex";
    var firstRowFirstColumnIcon = document.createElement('i');
    firstRowFirstColumnIcon.className = "far fa-user m-auto";
    firstRowFirstColumnIconContainer.appendChild(firstRowFirstColumnIcon);
    var firstRowFirstColumnTextTitle = document.createElement('h3');
    firstRowFirstColumnTextTitle.textContent = "Comments";
    var firstRowFirstColumnText = document.createElement('p');
    firstRowFirstColumnText.className = "lead mb-0";
    firstRowFirstColumnText.textContent = pStatistics.total_num_comments + " comments posted.";
    firstRowFirstColumnContent.appendChild(firstRowFirstColumnIconContainer);
    firstRowFirstColumnContent.appendChild(firstRowFirstColumnTextTitle);
    firstRowFirstColumnContent.appendChild(firstRowFirstColumnText);
    firstRowFirstColumn.appendChild(firstRowFirstColumnContent);

    // --- SECOND COLUMN
    var firstRowSecondColumn = document.createElement('div');
    firstRowSecondColumn.className = "col-lg-4";
    var firstRowSecondColumnContent = document.createElement('div');
    firstRowSecondColumnContent.className = "features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3";
    var firstRowSecondColumnIconContainer = document.createElement('div');
    firstRowSecondColumnIconContainer.className = "features-icons-icon d-flex";
    var firstRowSecondColumnIcon = document.createElement('i');
    firstRowSecondColumnIcon.className = "far fa-user m-auto";
    firstRowSecondColumnIconContainer.appendChild(firstRowSecondColumnIcon);
    var firstRowSecondColumnTextTitle = document.createElement('h3');
    firstRowSecondColumnTextTitle.textContent = "Score";
    var firstRowSecondColumnText = document.createElement('p');
    firstRowSecondColumnText.className = "lead mb-0";
    firstRowSecondColumnText.textContent = pStatistics.total_score + " total upvotes from all posts.";
    firstRowSecondColumnContent.appendChild(firstRowSecondColumnIconContainer);
    firstRowSecondColumnContent.appendChild(firstRowSecondColumnTextTitle);
    firstRowSecondColumnContent.appendChild(firstRowSecondColumnText);
    firstRowSecondColumn.appendChild(firstRowSecondColumnContent);

    // --- THIRD COLUMN
    var firstRowThirdColumn = document.createElement('div');
    firstRowThirdColumn.className = "col-lg-4";
    var firstRowThirdColumnContent = document.createElement('div');
    firstRowThirdColumnContent.className = "features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3";
    var firstRowThirdColumnIconContainer = document.createElement('div');
    firstRowThirdColumnIconContainer.className = "features-icons-icon d-flex";
    var firstRowThirdColumnIcon = document.createElement('i');
    firstRowThirdColumnIcon.className = "far fa-user m-auto";
    firstRowThirdColumnIconContainer.appendChild(firstRowThirdColumnIcon);
    var firstRowThirdColumnTextTitle = document.createElement('h3');
    firstRowThirdColumnTextTitle.textContent = "Awards";
    var firstRowThirdColumnText = document.createElement('p');
    firstRowThirdColumnText.className = "lead mb-0";
    firstRowThirdColumnText.textContent = pStatistics.total_awards + " total awards from all posts.";
    firstRowThirdColumnContent.appendChild(firstRowThirdColumnIconContainer);
    firstRowThirdColumnContent.appendChild(firstRowThirdColumnTextTitle);
    firstRowThirdColumnContent.appendChild(firstRowThirdColumnText);
    firstRowThirdColumn.appendChild(firstRowThirdColumnContent);

    firstRow.appendChild(firstRowFirstColumn);
    firstRow.appendChild(firstRowSecondColumn);
    firstRow.appendChild(firstRowThirdColumn);

    // --- SECOND ROW ---
    var secondRow = document.createElement('div');
    secondRow.className = "row";

    // --- FIRST COLUMN ---
    var secondRowFirstColumn = document.createElement('div');
    secondRowFirstColumn.className = "col-lg-4";
    var secondRowFirstColumnContent = document.createElement('div');
    secondRowFirstColumnContent.className = "features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3";
    var secondRowFirstColumnIconContainer = document.createElement('div');
    secondRowFirstColumnIconContainer.className = "features-icons-icon d-flex";
    var secondRowFirstColumnIcon = document.createElement('i');
    secondRowFirstColumnIcon.className = "far fa-user m-auto";
    secondRowFirstColumnIconContainer.appendChild(secondRowFirstColumnIcon);
    var secondRowFirstColumnTextTitle = document.createElement('h3');
    secondRowFirstColumnTextTitle.textContent = "Most liked comment";
    var secondRowFirstColumnText = document.createElement('p');
    secondRowFirstColumnText.className = "lead mb-0";
    var mostLikedSingleKey = Object.keys(pStatistics.most_liked_single)[0];
    secondRowFirstColumnText.textContent = "Comment by u/" + mostLikedSingleKey + " with " +
        pStatistics.most_liked_single[mostLikedSingleKey] + " likes received.";
    secondRowFirstColumnContent.appendChild(secondRowFirstColumnIconContainer);
    secondRowFirstColumnContent.appendChild(secondRowFirstColumnTextTitle);
    secondRowFirstColumnContent.appendChild(secondRowFirstColumnText);
    secondRowFirstColumn.appendChild(secondRowFirstColumnContent);

    // --- SECOND COLUMN ---
    var secondRowSecondColumn = document.createElement('div');
    secondRowSecondColumn.className = "col-lg-4";
    var secondRowSecondColumnContent = document.createElement('div');
    secondRowSecondColumnContent.className = "features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3";
    var secondRowSecondColumnIconContainer = document.createElement('div');
    secondRowSecondColumnIconContainer.className = "features-icons-icon d-flex";
    var secondRowSecondColumnIcon = document.createElement('i');
    secondRowSecondColumnIcon.className = "far fa-user m-auto";
    secondRowSecondColumnIconContainer.appendChild(secondRowSecondColumnIcon);
    var secondRowSecondColumnTextTitle = document.createElement('h3');
    secondRowSecondColumnTextTitle.textContent = "Most engaging comment";
    var secondRowSecondColumnText = document.createElement('p');
    secondRowSecondColumnText.className = "lead mb-0";
    var mostEngagedSingleKey = Object.keys(pStatistics.most_engaged_single)[0];
    secondRowSecondColumnText.textContent = "Comment by u/" + mostEngagedSingleKey + " with " +
        pStatistics.most_engaged_single[mostEngagedSingleKey] + " replies received.";
    secondRowSecondColumnContent.appendChild(secondRowSecondColumnIconContainer);
    secondRowSecondColumnContent.appendChild(secondRowSecondColumnTextTitle);
    secondRowSecondColumnContent.appendChild(secondRowSecondColumnText);
    secondRowSecondColumn.appendChild(secondRowSecondColumnContent);

    // --- THIRD COLUMN ---
    var secondRowThirdColumn = document.createElement('div');
    secondRowThirdColumn.className = "col-lg-4";
    var secondRowThirdColumnContent = document.createElement('div');
    secondRowThirdColumnContent.className = "features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3";
    var secondRowThirdColumnIconContainer = document.createElement('div');
    secondRowThirdColumnIconContainer.className = "features-icons-icon d-flex";
    var secondRowThirdColumnIcon = document.createElement('i');
    secondRowThirdColumnIcon.className = "far fa-user m-auto";
    secondRowThirdColumnIconContainer.appendChild(secondRowThirdColumnIcon);
    var secondRowThirdColumnTextTitle = document.createElement('h3');
    secondRowThirdColumnTextTitle.textContent = "Most awarded comment";
    var secondRowThirdColumnText = document.createElement('p');
    secondRowThirdColumnText.className = "lead mb-0";
    var mostAwardedSingleKey = Object.keys(pStatistics.most_awarded_single)[0];
    secondRowThirdColumnText.textContent = "Comment by u/" + mostAwardedSingleKey + " with " +
        pStatistics.most_awarded_single[mostAwardedSingleKey] + " awards received.";
    secondRowThirdColumnContent.appendChild(secondRowThirdColumnIconContainer);
    secondRowThirdColumnContent.appendChild(secondRowThirdColumnTextTitle);
    secondRowThirdColumnContent.appendChild(secondRowThirdColumnText);
    secondRowThirdColumn.appendChild(secondRowThirdColumnContent);

    secondRow.appendChild(secondRowFirstColumn);
    secondRow.appendChild(secondRowSecondColumn);
    secondRow.appendChild(secondRowThirdColumn);

    // --- THIRD ROW ---
    var thirdRow = document.createElement('div');
    thirdRow.className = "row";

    // --- FIRST COLUMN ---
    var thirdRowFirstColumn = document.createElement('div');
    thirdRowFirstColumn.className = "col-lg-4";
    var thirdRowFirstColumnContent = document.createElement('div');
    thirdRowFirstColumnContent.className = "features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3";
    var thirdRowFirstColumnIconContainer = document.createElement('div');
    thirdRowFirstColumnIconContainer.className = "features-icons-icon d-flex";
    var thirdRowFirstColumnIcon = document.createElement('i');
    thirdRowFirstColumnIcon.className = "far fa-user m-auto";
    thirdRowFirstColumnIconContainer.appendChild(thirdRowFirstColumnIcon);
    var thirdRowFirstColumnTextTitle = document.createElement('h3');
    thirdRowFirstColumnTextTitle.textContent = "Most liked redditor";
    var thirdRowFirstColumnText = document.createElement('p');
    thirdRowFirstColumnText.className = "lead mb-0";
    var mostLikedMultipleKey = Object.keys(pStatistics.most_liked_multiple)[0];
    thirdRowFirstColumnText.textContent = "u/" + mostLikedMultipleKey + " with " + 
        pStatistics.most_liked_multiple[mostLikedMultipleKey] + " likes received across all " + pStatistics.num_posts + " posts";
    thirdRowFirstColumnContent.appendChild(thirdRowFirstColumnIconContainer);
    thirdRowFirstColumnContent.appendChild(thirdRowFirstColumnTextTitle);
    thirdRowFirstColumnContent.appendChild(thirdRowFirstColumnText);
    thirdRowFirstColumn.appendChild(thirdRowFirstColumnContent);

    // --- SECOND COLUMN ---
    var thirdRowSecondColumn = document.createElement('div');
    thirdRowSecondColumn.className = "col-lg-4";
    var thirdRowSecondColumnContent = document.createElement('div');
    thirdRowSecondColumnContent.className = "features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3";
    var thirdRowSecondColumnIconContainer = document.createElement('div');
    thirdRowSecondColumnIconContainer.className = "features-icons-icon d-flex";
    var thirdRowSecondColumnIcon = document.createElement('i');
    thirdRowSecondColumnIcon.className = "far fa-user m-auto";
    thirdRowSecondColumnIconContainer.appendChild(thirdRowSecondColumnIcon);
    var thirdRowSecondColumnTextTitle = document.createElement('h3');
    thirdRowSecondColumnTextTitle.textContent = "Most engaging redditor";
    var thirdRowSecondColumnText = document.createElement('p');
    thirdRowSecondColumnText.className = "lead mb-0";
    var mostEngagedMultipleKey = Object.keys(pStatistics.most_engaged_multiple)[0];
    thirdRowSecondColumnText.textContent = "u/" + mostEngagedMultipleKey + " with " + 
        pStatistics.most_engaged_multiple[mostEngagedMultipleKey] + " comments received across all " + pStatistics.num_posts + " posts";
    thirdRowSecondColumnContent.appendChild(thirdRowSecondColumnIconContainer);
    thirdRowSecondColumnContent.appendChild(thirdRowSecondColumnTextTitle);
    thirdRowSecondColumnContent.appendChild(thirdRowSecondColumnText);
    thirdRowSecondColumn.appendChild(thirdRowSecondColumnContent);

    // --- THIRD COLUMN ---
    var thirdRowThirdColumn = document.createElement('div');
    thirdRowThirdColumn.className = "col-lg-4";
    var thirdRowThirdColumnContent = document.createElement('div');
    thirdRowThirdColumnContent.className = "features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3";
    var thirdRowThirdColumnIconContainer = document.createElement('div');
    thirdRowThirdColumnIconContainer.className = "features-icons-icon d-flex";
    var thirdRowThirdColumnIcon = document.createElement('i');
    thirdRowThirdColumnIcon.className = "far fa-user m-auto";
    thirdRowThirdColumnIconContainer.appendChild(thirdRowThirdColumnIcon);
    var thirdRowThirdColumnTextTitle = document.createElement('h3');
    thirdRowThirdColumnTextTitle.textContent = "Most awarded redditor";
    var thirdRowThirdColumnText = document.createElement('p');
    thirdRowThirdColumnText.className = "lead mb-0";
    var mostAwardedMultipleKey = Object.keys(pStatistics.most_awarded_multiple)[0];
    thirdRowThirdColumnText.textContent = "u/" + mostAwardedMultipleKey + " with " + 
        pStatistics.most_awarded_multiple[mostAwardedMultipleKey] + " awards received across all " + pStatistics.num_posts + " posts";
    thirdRowThirdColumnContent.appendChild(thirdRowThirdColumnIconContainer);
    thirdRowThirdColumnContent.appendChild(thirdRowThirdColumnTextTitle);
    thirdRowThirdColumnContent.appendChild(thirdRowThirdColumnText);
    thirdRowThirdColumn.appendChild(thirdRowThirdColumnContent);

    thirdRow.appendChild(thirdRowFirstColumn);
    thirdRow.appendChild(thirdRowSecondColumn);
    thirdRow.appendChild(thirdRowThirdColumn);

    eleStatisticsContainer.appendChild(h2title);
    eleStatisticsContainer.appendChild(firstRow);
    eleStatisticsContainer.appendChild(document.createElement('br'));
    eleStatisticsContainer.appendChild(secondRow);
    eleStatisticsContainer.appendChild(document.createElement('br'));
    eleStatisticsContainer.appendChild(thirdRow);
    eleStatisticsContainer.appendChild(document.createElement('br'));

    var mostUsedWordsTitleAssocArrayKeys = [];
    var mostUsedWordsTitleAssocArrayValues = [];

    for (let key in pStatistics.most_used_words_in_title){
        mostUsedWordsTitleAssocArrayKeys.push(key);
        mostUsedWordsTitleAssocArrayValues.push(pStatistics.most_used_words_in_title[key]);
    }

    var h2LeftChartTitle = document.createElement('h2');
    h2LeftChartTitle.textContent = "Most used words in post title";
    h2LeftChartTitle.className = "mb-5 mt-5";

    eleLeftChartContainer.insertBefore(h2LeftChartTitle, eleLeftChart);
    eleLeftChartContainer.insertBefore(document.createElement('br'), eleLeftChart);

    var leftBarChart = new Chart(leftChartCtx, {
        type: 'horizontalBar',
        data: {
            labels: mostUsedWordsTitleAssocArrayKeys,
            datasets: [{
                label: '# of usages',
                data: mostUsedWordsTitleAssocArrayValues,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ]}],
            },
        options: {
            responsive: true,
            mantainAspectRatio: true,
            scales: {
                yAxes: [{
                    categoryPercentage: 0.8,
                    barPercentage: 0.8,
                    gridLines: {
                        offsetGridLines: true
                    }
                }]
            }
        }
    });

    var mostUsedWordsBodyAssocArrayKeys = [];
    var mostUsedWordsBodyAassocArrayValues = [];

    for (let key in pStatistics.most_used_words_in_body){
        mostUsedWordsBodyAssocArrayKeys.push(key);
        mostUsedWordsBodyAassocArrayValues.push(pStatistics.most_used_words_in_body[key]);
    }

    var h2RightChartTitle = document.createElement('h2');
    h2RightChartTitle.textContent = "Most used words in post body";
    h2RightChartTitle.className = "mb-5 mt-5";

    eleRightChartContainer.insertBefore(h2RightChartTitle, eleRightChart);
    eleRightChartContainer.insertBefore(document.createElement('br'), eleRightChart);

    var rightBarChart = new Chart(rightChartCtx, {
        type: 'horizontalBar',
        data: {
            labels: mostUsedWordsBodyAssocArrayKeys,
            datasets: [{
                label: '# of usages',
                data: mostUsedWordsBodyAassocArrayValues,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ]}],
            },
        options: {
            responsive: true,
            mantainAspectRatio: true,
            scales: {
                yAxes: [{
                    categoryPercentage: 0.8,
                    barPercentage: 0.8,
                    gridLines: {
                        offsetGridLines: true
                    }
                }]
            }
        }
    });
}

function clearResults(){
    while (eleStatisticsContainer.firstChild) {
        eleStatisticsContainer.removeChild(eleStatisticsContainer.firstChild);
    }
    while (eleShowcaseContainer.firstChild) {
        eleShowcaseContainer.removeChild(eleShowcaseContainer.firstChild);
    }
    for(let i=0;i<eleLeftChartContainer.childNodes.length - 1;i++){
        eleLeftChartContainer.removeChild(eleLeftChartContainer.firstChild);
        eleRightChartContainer.removeChild(eleRightChartContainer.firstChild);
    }

    var rightBarChart = new Chart(rightChartCtx, {
        type: 'horizontalBar',
        data: [],
        options: [],
    });

    var leftBarChart = new Chart(leftChartCtx, {
        type: 'horizontalBar',
        data: [],
        options: [],
    });
    //eleRightChart.style.display = "none";
    //eleLeftChart.style.display = "none";
}