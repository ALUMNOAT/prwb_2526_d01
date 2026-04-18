$(() => {
  // --- 1. INJECTION DU HTML ---

  // On ajoute le code de la fenêtre surgissante (modal) directement à la fin du <body>.
  // On utilise les backticks (`) pour écrire du HTML sur plusieurs lignes proprement.
  $("body").append(`
        <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 15px; color: #333;">
                    <div class="modal-header">
                        <h5 class="modal-title">Unsaved Changes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        You have unsaved changes. Do you really want to leave this page?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Stay here</button>
                        <button type="button" id="execute-action" class="btn btn-danger">Leave / Save</button>
                    </div>
                </div>
            </div>
        </div>
    `);

  // --- 2. INITIALISATION DES VARIABLES ---

  // On crée une "instance" Bootstrap pour pouvoir faire .show() et .hide() plus tard
  const saveModal = new bootstrap.Modal("#confirmModal");

  // On sélectionne le formulaire par son ID
  const $form = $("#add_item");

  // On enregistre l'état du formulaire au chargement de la page (les valeurs initiales)
  // .serialize() transforme le formulaire en une chaîne de texte (ex: "title=iPhone&price=500")
  const initialFormData = $form.serialize();

  // Variable "mémoire" : on y stockera une fonction à exécuter seulement si l'utilisateur confirme (callback)
  let pendingAction = null;

  // Flag pour ignorer le beforeunload
  let isSubmitting = false;

  // Petite fonction utilitaire : renvoie 'true' si le formulaire actuel est différent du début
  const isDirty = () => initialFormData !== $form.serialize();

  // --- 3. COMPORTEMENTS ---

  // --- SCÉNARIO 1 : CLIC SUR LE BOUTON 'SAVE' (Disquette) ---
  $('button[form="add_item"]').on("click", function (e) {
    // Si le formulaire a été modifié (isDirty est vrai)
    if (isDirty()) {
      e.preventDefault(); // On stoppe l'envoi immédiat du formulaire au serveur

      // On prépare le texte de la modal pour une sauvegarde
      $(".modal-body").text("Are you sure you want to save these changes?");
      $("#execute-action")
        .text("Save Changes") //.removeClass('btn-danger').addClass('btn-primary')
        .css({
          "background-color": "#007bff",
          "border-color": "#007bff",
          "color": "white"
        });

      // On stocke l'action : "Envoyer le formulaire"
      pendingAction = () => {
        isSubmitting = true; // On autorise la sortie
        $form.submit();
      };
      saveModal.show(); // On affiche la modal
    }
    // Si pas de modif, on ne fait rien, le formulaire part normalement
  });

  // --- SCÉNARIO 2 : CLIC SUR UN LIEN (Retour, Menu, etc.) ---
  $("a").on("click", function (e) {
    const url = $(this).attr("href"); // On récupère l'adresse de destination

    // Si modifié ET que le lien mène quelque part
    if (isDirty() && url && url !== "#") {
      e.preventDefault(); // On empêche le navigateur de changer de page tout de suite

      // On prépare le texte de la modal pour un abandon des modifs
      $(".modal-body").text("You have unsaved changes. If you leave, they will be lost.");
      $("#execute-action")
        .text("Leave Page") //.removeClass('btn-primary').addClass('btn-danger')
        .css({
          "background-color": "#dc3545",
          "border-color": "#dc3545",
          color: "white",
        });

      // On stocke l'action : "Changer d'URL"
      pendingAction = () => {
        isSubmitting = true; // On autorise la sortie
        window.location.href = url;
      };
      saveModal.show(); // On affiche la modal
    }
  });

  // --- 4. EXÉCUTION DE L'ACTION EN ATTENTE ---
  // Quand l'utilisateur clique sur le bouton bleu/rouge de la modal
  $("#execute-action").on("click", function () {
    if (pendingAction) {
      pendingAction(); // On exécute la fonction qu'on a mise en mémoire plus haut
    }
  });

  // --- 5. SÉCURITÉ NAVIGATEUR (Fermer l'onglet) ---
  // Si l'utilisateur essaie de fermer l'onglet ou de taper une autre URL à la main
  $(window).on("beforeunload", function () {
    // On n'affiche l'alerte QUE si c'est sale ET qu'on n'est pas en train de sortir via la modal
    if (isDirty() && !isSubmitting) {
      // Affiche une alerte système (le texte est ignoré par les navigateurs modernes, mais l'alerte apparaît)
      return "You have unsaved changes!";
    }
  });
});
