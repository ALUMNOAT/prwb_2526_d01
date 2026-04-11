$(() => {
  const $form = $("#add_item");
  const $inputs = $(
    "#title, #description, #days, #starting_bid, #buy_now_price, #sale_price",
  );

  function validateForm() {
    $.post(
      "item/validate_full_item_ajax", $form.serialize(),
      function (data) {
        $(".error-message").hide().text("");
        $(".input").css("border", "1px solid green");

        if (data.errors.length > 0) {
          $("button[type='submit']").prop("disabled", true);

          // si l'erreur dans le validator a la phrase qui commence par (voir Item.php-> validate_title, ...)
          data.errors.forEach((msg) => {
            if (msg.includes("Title")) {
              $("#error-title").text(msg).show();
              $("#title").css("border", "2px solid red");
            }
            if (msg.includes("Description")) {
              $("#error-description").text(msg).show();
              $("#description").css("border", "2px solid red");
            }
            if (msg.includes("Starting bid")) {
              $("#error-starting-bid").text(msg).show();
              $("#starting_bid").css("border", "2px solid red");
            }
            if (msg.includes("Buy now price")) {
              $("#error-buy-now-price").text(msg).show();
              $("#buy_now_price").css("border", "2px solid red");
            }
            if (
              msg.includes("Sale Price") ||
              msg.includes("at least one price")
            ) {
              $("#error-sale-price").text(msg).show();
              $("#sale_price").css("border", "2px solid red");
            }
            if (msg.includes("requires a Starting Bid")) {
              $("#error-buy-now-price").text(msg).show();
              $("#buy_now_price").css("border", "2px solid red");
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
