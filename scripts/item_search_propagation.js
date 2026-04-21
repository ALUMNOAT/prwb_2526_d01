let return_back, pathname, encoded_value;

$(document).ready(function() {
    //https://www.sitepoint.com/jquery-current-page-url/
    //https://stackoverflow.com/questions/30314447/how-do-you-test-for-nan-in-javascript
    pathname = $(location).attr("href");
    last_param = pathname.split("/").pop();

    if(isNaN(parseInt(last_param))) {
        let base_url = $("#return_back").attr("href");
        $("#return_back").attr("href", base_url + "/1/" + last_param);
    }
});