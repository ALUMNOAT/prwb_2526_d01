let all_items;


$(() => {
  // Listen to ALL inputs in the search bar
  $("#fulltext-search, #search-field, #category-field").on(
    "input change",
    function () {
      const value = $("#fulltext-search").val().toLowerCase();
      const field = $("#search-field").val();
      const categoryFilter = $("#category-field").val(); // Get the selected category

      $("div.content > h1").each(function () {
        const $heading = $(this);
        const $browse = $heading.next("div.browse");
        let visible = 0;

        $browse.children().each(function () {
          const $card = $(this);
          const title = $card.find(".container-text b").text().toLowerCase();
          const owner = $card
            .find(".container-text .attribution")
            .first()
            .text()
            .toLowerCase();
          const description = (
            $card.attr("data-description") ?? ""
          ).toLowerCase();

          // Get categories from the data attribute we fixed earlier
          const categories = (
            $card.attr("data-categories") ?? ""
          ).toLowerCase();

          const check = (str) => str.indexOf(value) > -1;

          // Combined logic
          const matchesText =
            (field === "all" &&
              (check(title) || check(owner) || check(description))) ||
            (field === "title" && check(title)) ||
            (field === "owner" && check(owner)) ||
            (field === "description" && check(description));

          const matchesCategory =
            categoryFilter === "all" ||
            categories.indexOf(categoryFilter.toLowerCase()) > -1;

          const matches = (value === "" || matchesText) && matchesCategory;

          $card.toggle(matches);
          if (matches) visible++;
        });

        $heading.toggle(visible > 0);
        $browse.toggle(visible > 0);
      });

      $("#no-result-msg").remove();
      if (value !== "" && $("div.browse .card:visible").length === 0) {
        $("div.content").append('<p id="no-result-msg">No item found.</p>');
      }
    },
  );
});

function get_all_items() {
  $.get({
    url: "item/get_all_items_service",
    dataType: "json",
    success: function(data) {
      all_items = data;
      console.log(data);
    }
  })
}
