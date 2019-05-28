window.onload = boot;

var ID_URL_FORM = "idUrlForm",
    ID_PARAMETERS_FORM = "idParametersForm",
    ID_URL="idInputUrl",
    ID_QUERY = "idInputQuery",
    ID_INPUT_SUBREDDIT = "idInputSubreddit",
    ID_SELECT_CATEGORY = "idSelectCategory",
    ID_SELECT_TIME = "idSelectTime",
    ID_LIMIT = "idInputLimit",
    ID_BTN_PARAMETERS = "idBtnParameters";

var eleUrlForm, eleUrl, eleParametersForm, eleInputSubreddit, eleSelectCategory, eleSelectTime,
    eleLimit, eleQuery, eleBtnParamters;
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

    var objects = [eleUrlForm, eleUrl, eleInputSubreddit, eleParametersForm, eleSelectCategory,
        eleSelectTime, eleQuery, eleLimit, eleBtnParamters];
    var bAllOk = allOk(objects);
    if (!bAllOk) {
        alert("There is 1+ object(s) with a problem.");
        return;
    }// if
    
    eleParametersForm.onsubmit = sendParametersRequest;
    eleUrlForm.onsubmit = sendURLRequest;
}// boot

function sendURLRequest(){
    ajax("POST", URL_DO_SERVICO + "/searchLink", eleUrlForm);
    return false;
}

function sendParametersRequest(){
    ajax("POST", URL_DO_SERVICO + "/searchSubreddit", eleParametersForm);
    changeBtnState("searching");
    return false;
}

function ajax(pType, pPostUrl, pObjectForm) {
    if (pType == "POST" || pType == "GET") {
        var oReq = new XMLHttpRequest();
        if (oReq) {
            oReq.onload = function () {
                //This is where you handle what to do with the response.
                //The actual data is found on this.responseText
                alert("Search completed!");
                changeBtnState("success");
                console.log(this.responseText); //Will alert: 42
            };
            oReq.open(pType, pPostUrl, true);
            var formData = new FormData(pObjectForm);
            oReq.send(formData);
            
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