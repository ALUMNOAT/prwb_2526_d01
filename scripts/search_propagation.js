let search_bar, search_field, search_value, encoded_value, card_link;

$(document).ready(async function() {
    set_variables();
    await set_search_bar();
    set_events();
    //set_search_bar();
});

function set_variables() {
    //https://stackoverflow.com/questions/10659097/jquery-get-selected-option-from-dropdown
    search_bar = $("#fulltext-search");
    search_field = $("#search-field");

    let pathname = $(location).attr("href");
    let last_param = pathname.split("/").pop();

    if(isNaN(parseInt(last_param)) && (last_param != "browse" || last_param != "myitems" || last_param != "my_items")) {
        encoded_value = last_param;
    }

}

async function set_events() {
    //https://stackoverflow.com/questions/846954/change-url-and-redirect-using-jquery
    //https://stackoverflow.com/questions/2805742/how-to-update-append-to-an-href-in-jquery
    await search_bar.on("keyup", function () {
        search_value = $(this).val();
        set_encoded_value();
    });

    $(".card > a").on("click", function() {
        if(typeof encoded_value !== "undefined") {
            $(this).attr("href", $(this).attr("href") + "/1/" + encoded_value);
        }
    })

    $(".card").on("click", function() {
        console.log($(this).attr("data-description"));
    });

    /*$(".card > a").remove();
    item_cards = $(".card");*/
}

async function set_encoded_value() {
    //https://api.jquery.com/text/#:~:text=To%20set%20or%20get%20the,html()%20method.
    $.post("item/url_safe_encode_service/", {search: search_value},
        function (data, textStatus, jqXHR) {
            encoded_value = data.response;
            console.log(encoded_value);
        },
        "json"
    );
}

async function get_decoded_value(encoded_value) {
    let decoded_value = undefined;

    await $.get("item/url_safe_decode_service/"+encoded_value,
        function (data) {
            decoded_value = data.response;
        },
        "json"
    );

    console.log(decoded_value);

    return decoded_value;
}

async function set_search_bar() {
    //https://www.sitepoint.com/jquery-current-page-url/
    //https://stackoverflow.com/questions/30314447/how-do-you-test-for-nan-in-javascript
    //https://api.jquery.com/change/#trigger2
    pathname = $(location).attr("href");
    last_param = pathname.split("/").pop();

    console.log(pathname);
    console.log(last_param);

    if(isNaN(parseInt(last_param)) && (last_param != "browse" || last_param != "myitems" || last_param != "my_items")) {
        search_bar.val(await get_decoded_value(last_param));
        search_bar.trigger("change");
    }
}