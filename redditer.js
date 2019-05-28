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
    ID_SHOWCASE_CONTAINER = "idShowcaseContainer";

var eleUrlForm, eleUrl, eleParametersForm, eleInputSubreddit, eleSelectCategory, eleSelectTime,
    eleLimit, eleQuery, eleBtnParamters, eleShowcaseContainer;
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

    var objects = [eleUrlForm, eleUrl, eleInputSubreddit, eleParametersForm, eleSelectCategory,
        eleSelectTime, eleQuery, eleLimit, eleBtnParamters, eleShowcaseContainer];
    var bAllOk = allOk(objects);
    if (!bAllOk) {
        alert("There is 1+ object(s) with a problem.");
        return;
    }// if
    
    eleParametersForm.onsubmit = sendParametersRequest;
    eleUrlForm.onsubmit = sendURLRequest;
}// boot

function sendURLRequest(){
    ajax("POST", URL_DO_SERVICO + "/searchLink", eleUrlForm, function () {
        var start = this.responseText.indexOf("[");
        var json = this.responseText.substr(start);
        var elementsArray = JSON.parse(json);
        for (var element of elementsArray){
            var title = createTitleWithHyperlink(element.title, element.postUrl);
            var info = createInfoSectionString(element.author, element.subreddit, element.timePassed);
            createShowcaseElement(title, info, element.body);
        }
        changeBtnState("success");
        eleShowcaseContainer.scrollIntoView({block: 'start', behavior: 'smooth'});
    });
    changeBtnState("searching");
    return false;
}

function sendParametersRequest(){
    ajax("POST", URL_DO_SERVICO + "/searchSubreddit", eleParametersForm, function () {
        var start = this.responseText.indexOf("[");
        var json = this.responseText.substr(start);
        var elementsArray = JSON.parse(json);
        for (var element of elementsArray){
            var title = createTitleWithHyperlink(element.title, element.postUrl);
            var info = createInfoSectionString(element.author, element.subreddit, element.timePassed);
            createShowcaseElement(title, info, element.body);
        }
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
                oReq.send("purl=" + pObject);
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

function createShowcaseElement(pTitle, pInfo, pBody){
    /*<div class="row no-gutters">
        <div class="col-lg-12 order-lg-1 my-auto showcase-text">
            <h2> title </h2>
            <p> Posted by ... </p
            <p class="lead mb-0"> body </p>
        </div>
    </div>*/
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
        ajax("POST", URL_DO_SERVICO + "/postToTwitter", postUrl, function () {
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