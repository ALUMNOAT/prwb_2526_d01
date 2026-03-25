$(() => {
  // On insère la barre de recherche en haut du contenu
  $("div.content").prepend(`
    <div class="search-bar">
      <input type="text" id="fulltext-search" placeholder="Search..." autocomplete="off">
    </div>
  `);

  // À chaque frappe clavier, on récupère le texte saisi et on filtre
  $("#fulltext-search").on("input", function () {
    const pattern = $(this).val().trim().toLowerCase();
    filterItems(pattern);
  });

  function filterItems(pattern) {
    let totalVisible = 0;

    // On parcourt chaque titre de section
    // $("div.content > h1") = seulement les h1 ENFANTS DIRECTS de div.content (donc pas les petits enfants)
    $("div.content > h1").each(function () {
      const $heading = $(this);
      const $browse = $heading.next("div.browse"); // le conteneur des cartes sous ce titre (ici les 16 item_card.php par exemple)

      // Pour chaque carte de la section, on vérifie si elle contient le texte recherché
      let sectionVisible = 0;
      $browse.children().each(function () {
        const title = $(this).find(".container-text b").text().toLowerCase();

        const owner = $(this)
          .find(".container-text .attribution")
          .first() //2nd "attribution" est current bid. Donc on prend le 1er
          .text()
          .toLowerCase();

        // $(this).data("description") = $(this).attr("data-description") mais dur a se rappeler
        // plus facile de se dire que .find() = element html et balises
        // .attr() = pour les attributs

        const description = ($(this).attr("data-description") ?? "").toLowerCase();

        const matches =
          pattern === "" ||
          title.includes(pattern) ||
          owner.includes(pattern) ||
          description.includes(pattern);

        $(this).toggle(matches); // affiche ou cache la carte selon le résultat
        if (matches) sectionVisible++;
      });

      // Si aucune carte de la section ne correspond, on cache aussi le titre
      $heading.toggle(sectionVisible > 0);
      $browse.toggle(sectionVisible > 0);
      totalVisible += sectionVisible;
    });

    // On supprime l'ancien message "aucun résultat" s'il existe, pour éviter les doublons
    $("#no-result-msg").remove();

    // Si rien n'est visible et que l'utilisateur a bien tapé quelque chose, on affiche le message
    if (totalVisible === 0 && pattern !== "") {
      $("div.content").append('<p id="no-result-msg">No item found.</p>');
    }
  }
});

/* $(() => {
  // On insère la barre de recherche en haut du contenu
  $("div.content").prepend(`
    <div class="search-bar">
      <input type="text" id="fulltext-search" placeholder="Search items..." autocomplete="off">
    </div>
  `);

  // À chaque frappe clavier, on récupère le texte saisi et on filtre
  $("#fulltext-search").on("input", function () {
    const pattern = $(this).val().trim().toLowerCase();
    filterItems(pattern);
  });

  function filterItems(pattern) {
    let totalVisible = 0;

    // On parcourt chaque titre de section (Active, Unsold, Sold...)
    $("div.content > h1").each(function () {
      const $heading = $(this);
      const $browse = $heading.next("div.browse"); // le conteneur des cartes sous ce titre

      // Pour chaque carte de la section, on vérifie si elle contient le texte recherché
      let sectionVisible = 0;
      $browse.children().each(function () {
        const matches = pattern === "" || $(this).text().toLowerCase().includes(pattern);
        $(this).toggle(matches); // affiche ou cache la carte selon le résultat
        if (matches) sectionVisible++;
      });

      // Si aucune carte de la section ne correspond, on cache aussi le titre
      $heading.toggle(sectionVisible > 0);
      $browse.toggle(sectionVisible > 0);
      totalVisible += sectionVisible;
    });

    // On supprime l'ancien message "aucun résultat" s'il existe, pour éviter les doublons
    $("#no-result-msg").remove();

    // Si rien n'est visible et que l'utilisateur a bien tapé quelque chose, on affiche le message
    if (totalVisible === 0 && pattern !== "") {
      $("div.content").append('<p id="no-result-msg">No item found.</p>');
    }
  }
}); */
