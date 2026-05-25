let all_items, all_categories;


$(() => {
  //get_all_items();
  get_all_categories();
  
  $("div.content").prepend(`
    <div class="search-bar">

    <!-- Champ texte où l'utilisateur tape sa recherche -->
      <input type="text" id="fulltext-search" placeholder="Search..." autocomplete="off">

      <!-- Menu déroulant pour choisir sur quel champ filtrer -->
      <select id="search-field">
        <option value="all">All</option>
        <option value="title">Title</option>
        <option value="owner">Owner</option>
        <option value="description">Description</option>
      </select>

      <!-- Menu déroulant pour choisir sur quel champ filtrer -->
      <select id="category-field">
        <option value="all_categories">All</option>
        <option value="title">Title</option>
        <option value="owner">Owner</option>
        <option value="description">Description</option>
      </select>

    </div>
  `);

  /* Sur la barre de recherche et sur le filtre si on fait un changement -> fonction */
  $("#fulltext-search, #search-field").on("input change", function () {
    const value = $("#fulltext-search").val().toLowerCase();
    const field = $("#search-field").val();

    /* On entre dans les séparateurs MY BIDS et AVAILABLE ITEMS (view_browse comme exemple) */
    $("div.content > h1").each(function () {
      const $heading = $(this); // ici le titre (<h1> My bids <h1> comme exemple)
      const $browse = $heading.next("div.browse"); // On entre dans item_card
      let visible = 0; // 0 cartes visibles pour l'instant

      // Vu qu'on est rentré dans item_card, on utilse les div des item_cards
      $browse.children().each(function () {
        const title = $(this).find(".container-text b") // on cherche <b> (bold) dans div class="container-text"
          .text()
          .toLowerCase();

        const owner = $(this).find(".container-text .attribution")
          .first()
        // "Current bid" utilise aussi la classe .attribution, sans le .first(),
        // on récupèrerait aussi le prix du bid par erreur
          .text()
          .toLowerCase();

        // l'attribut de la description est dans la <div class="card"> (le $(this))
        // On aurait du le mettre dans le <div class="container-text">
        // et du coup on aurait du écrire comme dans les autres variables
        // $(this).find(".container-text").attr("data-description")
        const description = ($(this).attr("data-description") ?? "")
          .toLowerCase();

        // .indexOf() retourne la position de "value" dans "str", ou -1 si absent
        // > -1 signifie "value est contenu dans str"
        const check = (str) => str.indexOf(value) > -1;

        /* matches = vrai si...
           ├── input vide      → on affiche tout OU
           ├── field = "all"   ET match tout  OU
           ├── field = "title" ET match titre OU
           ├── field = "owner" ET match owner OU
           └── field = "desc"  ET match desc  OU */

        const matches =
          value === "" ||
          (field === "all" && (check(title) || check(owner) || check(description))) ||
          (field === "title" && check(title)) ||
          (field === "owner" && check(owner)) ||
          (field === "description" && check(description));

        $(this).toggle(matches);
        if (matches) visible++;
      });

      $heading.toggle(visible > 0);
      $browse.toggle(visible > 0);
    });

    $("#no-result-msg").remove();
    if (value !== "" && $("div.browse .card:visible").length === 0) {
      $("div.content").append('<p id="no-result-msg">No item found.</p>');
    }
  });
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

async function get_all_categories() {
  $.get({
    url: "item/get_all_categories_service",
    dataType: "json",
    success: function(data) {
      all_categories = data.data;
      console.log(data);
      console.log(all_categories);
    }
  })
}
