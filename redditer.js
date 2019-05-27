window.onload = boot;

var ID_URL_FORM = "idUrlForm",
    ID_PARAMETERS_FORM = "idParametersForm",
    ID_URL="idInputUrl",
    ID_INPUT_SUBREDDIT = "idInputSubreddit",
    ID_SELECT_CATEGORY = "idSelectCategory",
    ID_SELECT_TIME = "idSelectTime";

var eleUrlForm, eleUrl, eleParametersForm, eleInputSubreddit, eleSelectCategory, eleSelectTime;
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

    var objects = [eleUrlForm, eleUrl, eleInputSubreddit, eleParametersForm, eleSelectCategory, eleSelectTime];
    var bAllOk = allOk(objects);
    if (!bAllOk) {
        alert("There is 1+ object(s) with a problem.");
        return;
    }// if
    console.log("FODASS");
    eleParametersForm.onsubmit = sendParametersRequest;
    eleUrlForm.onsubmit = sendURLRequest;
}// boot

function sendURLRequest(){
    console.log("ASHAS");
    ajax("POST", URL_DO_SERVICO + "/searchLink", eleUrlForm);
}

function sendParametersRequest(){
    console.log("OMEGA");
    ajax("POST", URL_DO_SERVICO + "/searchSubreddit", eleParametersForm);
}

function ajax(pType, pPostUrl, pObjectForm) {
    if (pType == "POST" || pType == "GET") {
        var oReq = new XMLHttpRequest();
        if (oReq) {
            oReq.onload = function () {
                //This is where you handle what to do with the response.
                //The actual data is found on this.responseText

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