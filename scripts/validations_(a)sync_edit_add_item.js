let description_max_length = 10000, 
    description_min_length = 3,
    sale_duration_default = 7,
    sale_duration_max = 365,
    sale_duration_min = 1,
    title_max_length = 255,
    title_min_length = 3,
    category_per_item_max_count = 3;

async function get_rules() {
    $.get({
      url: "item/get_rules_service",
      dataType: "json",
      success: function(data) {
        description_max_length = data.description_max_length, 
        description_min_length = data.description_min_length,
        sale_duration_default = data.sale_duration_default,
        sale_duration_max = data.sale_duration_max,
        sale_duration_min = data.sale_duration_min,
        title_max_length = data.title_max_length,
        title_min_length = data.title_min_length,
        category_per_item_max_count = data.category_per_item_max_count;
      }
  })
}

$(() => {
  get_rules();

  const $form = $("#add_item");
  const $inputs = $("#title, #description, #days, #starting_bid, #buy_now_price, #sale_price, input[name='categories\\[\\]']");
  const $submitBtn = $("button[type='submit']");

  /**
   * Fonction de Debounce
   * Retarde l'exécution de la fonction 'func' jusqu'à ce que 'wait' millisecondes
   * se soient écoulées depuis la dernière fois qu'elle a été appelée.
   */
  function debounce(func, wait) {
    let timeout;
    return function(...args) {
      const context = this;
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(context, args), wait);
    };
  }

  /**
   * Logique principale de validation AJAX
   */
  function validateForm() {
    // Récupération de l'ID dans l'URL (ex: /item/edit/42 -> 42)
    const urlParts = window.location.pathname.split('/');
    const itemId = urlParts[urlParts.length - 1];

    // --- ICI : Debug de ce qui est dans le formulaire ---
    const formData = new FormData($form[0]);
    console.log("Données envoyées au serveur :");
    for (let [key, value] of formData.entries()) {
        console.table(key, value);
    }
    // ----------------------------------------------------

    $.post(
      "item/validate_full_item_service/" + itemId,
      $form.serialize(),
      function (data) {
        // Réinitialisation visuelle avant d'afficher les nouvelles erreurs
        $(".error-message").hide().text("");
        $inputs.css("border", "1px solid green");

        if (data.errors && Object.keys(data.errors).length > 0) {
          $submitBtn.prop("disabled", true);

          console.log("Erreurs reçues du serveur :", data.errors);

          // Parcours des erreurs renvoyées par le PHP
          Object.keys(data.errors).forEach((key) => {
            let msg = data.errors[key];

            if (key === "title") {
              $("#error-title").text(msg).show();
              $("#title").css("border", "2px solid red");
            }
            if (key === "description") {
              $("#error-description").text(msg).show();
              $("#description").css("border", "2px solid red");
            }
            if (key === "days") {
              $("#error-sale-duration").text(msg).show();
              $("#days").css("border", "2px solid red");
            }
            if (key === "starting_bid_missing" || key === "starting_bid") {
              $("#error-starting-bid").text(msg).show();
              $("#starting_bid").css("border", "2px solid red");
            }
            if (key === "buy_now_price") {
              $("#error-buy-now-price").text(msg).show();
              $("#buy_now_price").css("border", "2px solid red");
            }
            if (key === "sale_price" || key === "sale_option_conflict") {
              $("#error-sale-price").text(msg).show();
              $("#sale_price").css("border", "2px solid red");
            }
            if (key === "categories[]") {
              $("#error-categories").text(msg).show();
              $('input[name="categories\\[\\]"]').css("outline", "2px solid red");

              // Sélectionne la modale existante
              const modalEl = document.getElementById('categoryErrorModal');
              const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
              modal.show();
            }
          });
        } else {
          // Aucune erreur : on réactive le bouton
          $submitBtn.prop("disabled", false);
        }
      },
      "json"
    );
  }

  // Création de la version "debouncée" (400ms de délai)
  const debouncedValidation = debounce(validateForm, 400);

  // Événement INPUT : l'utilisateur tape (on utilise le debounce pour épargner le serveur)
  $inputs.on("input", function () {
    $(this).addClass("touched");
    debouncedValidation();
  });

  // Événement BLUR : l'utilisateur quitte le champ (on valide immédiatement)
  $inputs.on("blur", function () {
    $(this).addClass("touched");
    validateForm();
  });

  // Forcer le nettoyage quand la modale est fermée
  $('#categoryErrorModal').on('hidden.bs.modal', function () {
      $('body').removeClass('modal-open');
      $('.modal-backdrop').remove(); // Supprime le fond gris récalcitrant
  });
});
