let search_bar, search_field, search_value, field_value, category_field, category_value, encoded_value, card_link;

$(document).ready(async function() {
    await set_variables();
    set_events();
});

async function set_variables() {
    //https://stackoverflow.com/questions/10659097/jquery-get-selected-option-from-dropdown
    //https://stackoverflow.com/questions/13343566/set-select-option-selected-by-value
    search_bar = $("#fulltext-search");
    search_field = $("#search-field");
    category_field = $("#category-field");

    let pathname = $(location).attr("href");
    let last_param = pathname.split("/").pop();

    if(isNaN(parseInt(last_param)) && (last_param != "browse" || last_param != "myitems" || last_param != "my_items")) {
        encoded_value = last_param;
        await get_decoded_values(last_param);

        if(search_value === "") {
            let clean_url = $(location).attr("href").replace("/"+encoded_value, "");
            $(location).attr("href", clean_url);

        } else {
            search_bar.val(search_value);
            search_field.val(field_value);
            category_field.val(category_value);
            search_field.children().each(function() {
                let option = $(this);
                if(option.val().toLowerCase() == field_value)
                    option.attr("selected","selected");
            });
            
            search_bar.trigger("change");
        }
    }
}

async function set_events() {
    //https://stackoverflow.com/questions/846954/change-url-and-redirect-using-jquery
    //https://stackoverflow.com/questions/2805742/how-to-update-append-to-an-href-in-jquery
    await search_bar.on("keyup", function () {
        search_value = $(this).val().trim();
        set_encoded_value();
    });

    await search_field.on("change", function () {
        field_value = search_field.val();
        set_encoded_value();
    });

    await category_field.on("change", function () {
        category_value = category_field.val();
        set_encoded_value();
    });

    $(".card > a").on("click", function() {
        if(typeof encoded_value !== "undefined" && search_value !== "") {
            $(this).attr("href", $(this).attr("href") + "/1/" + encoded_value);
        }
    });
}

async function set_encoded_value() {
    //https://api.jquery.com/text/#:~:text=To%20set%20or%20get%20the,html()%20method.
    let data_to_encode = field_value + "|" + search_value + "|" + category_value;

    $.post("item/url_safe_encode_service/", {search: data_to_encode},
        function (data, textStatus, jqXHR) {
            encoded_value = data.response;
        },
        "json"
    );
}

async function get_decoded_values(encoded_value) {
    await $.get("item/url_safe_decode_service/"+encoded_value,
        function (data) {
            search_value = data.response.search_value.trim();
            field_value = data.response.field;
            category_value = data.response.category;
            console.log(field_value + "    " + search_value + "    " + category_value)
        },
        "json"
    );
}