let search_bar, search_field, search_value, encoded_value, card_link;

$(document).ready(function() {
    set_variables();
    set_events();
});

function set_variables() {
    //https://stackoverflow.com/questions/10659097/jquery-get-selected-option-from-dropdown
    search_bar = $("#fulltext-search");
    search_field = $("#search-field");
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