$(() => {
  const $form = $("#add_item");
  const $inputs = $(
    "#title, #description, #days, #starting_bid, #buy_now_price, #sale_price",
  );

  function validateForm() {
    // On récupère l'ID 38 directement depuis l'URL de la page actuelle
    const urlParts = window.location.pathname.split('/');
    const itemId = urlParts[urlParts.length - 1];
    
    $.post(
      "item/validate_full_item_ajax/" + itemId, $form.serialize(),
      function (data) {
        $(".error-message").hide().text("");
        $(".input").css("border", "1px solid green");


        if (Object.keys(data.errors).length > 0) {
          $("button[type='submit']").prop("disabled", true);

          // si l'erreur dans le validator a la phrase qui commence par (voir Item.php-> validate_title, ...)
          Object.keys(data.errors).forEach((key) => {
            let msg = data.errors[key];
            
            if (key == "title") {
              $("#error-title").text(msg).show();
              $("#title").css("border", "2px solid red");
            }
            if (key == "description") {
              $("#error-description").text(msg).show();
              $("#description").css("border", "2px solid red");
            }
            if (key == "days") {
              $("#error-sale-duration").text(msg).show();
              $("#days").css("border", "2px solid red");
            }
            if (key == "starting_bid_missing" || key == "starting_bid") {
              $("#error-starting-bid").text(msg).show();
              $("#starting_bid").css("border", "2px solid red");
            }
            if (key == "buy_now_price") {
              $("#error-buy-now-price").text(msg).show();
              $("#buy_now_price").css("border", "2px solid red");
            }
            if (
              key == "sale_price" ||
              key == "sale_option_conflict"
            ) {
              $("#error-sale-price").text(msg).show();
              $("#sale_price").css("border", "2px solid red");
            }
          });
        } else {
          $("button[type='submit']").prop("disabled", false);
        }
      },
      "json",
    );
  }

  $inputs.on("input blur", function () {
    $(this).addClass("touched");
    validateForm();
  });

  /*

  // On sélectionne les spans d'erreur
  const $errorTitle = $("#error-title");
  const $errorDesc = $("#error-description");
  const $errorSaleDuration = $("#error-sale-duration");
  const $errorStartingBid = $("#error-starting-bid");
  const $errorBuyNowPrice = $("#error-buy-now-price");
  const $errorSalePrice = $("#error-sale-price");

  // On écrit "non" à l'intérieur
  $errorTitle.text("non");
  $errorDesc.text("non");
  $errorSaleDuration.text("non");
  $errorStartingBid.text("non");
  $errorBuyNowPrice.text("non");
  $errorSalePrice.text("non");

  // On les affiche (équivalent de display: block ou inline)
  $errorTitle.show();
  $errorDesc.show();
  $errorSaleDuration.show();
  $errorStartingBid.show();
  $errorBuyNowPrice.show();
  $errorSalePrice.show();

  */
});
