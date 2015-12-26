
/*
 IMPORTANT: THIS FILE IS DEPRECATED, SEE CURRENT IMPLEMENTATION IN PAGE-HEADERS.PHP
*/


function removeParam(key, sourceURL) {
    var rtn = sourceURL.split("?")[0],
        param,
        params_arr = [],
        queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
    if (queryString !== "") {
        params_arr = queryString.split("&");
        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split("=")[0];
            if (param === key) {
                params_arr.splice(i, 1);
            }
        }
        rtn = rtn + "?" + params_arr.join("&");
    }
    return rtn;
}
    
function btn_shutdown() {
    var x;
    if (confirm("Are you sure you want to shutdown the BeagleTorrent?") == true) {
        //var link = "reboot.php?user=" + $_GET('user');
        window.open(link);
    } else {
        /* nothing to do */
    }
}

function auto_closewindow()
{
    alert("Please close this window since it's not valid anymore. Please reopen BeagleTorrent home page.");
    
    // unfortunately scripted-close of a browser window is not allowed anymore, 
    // see http://stackoverflow.com/questions/19761241/window-close-and-self-close-do-not-close-the-window-in-chrome 
    //var ww = window.open(window.location, '_self');
    //ww.close();
    //close(); 
}

function auto_refreshwindow()
{
    var newUrl = removeParam("php_action_status", window.location.href);
    //console.log("the new url is " + newUrl);
    
    // just changing the location.href is ok, no need to call also reload:
    window.location.href = newUrl;
    //location.reload();
}

