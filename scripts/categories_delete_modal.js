document.addEventListener("DOMContentLoaded", () => {
  // 1. INJECTION DU HTML
  document.body.insertAdjacentHTML(
    "beforeend",
    `
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 15px; color: #333;">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Do you want to delete this category?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                        <button type="button" id="execute-action" class="btn btn-danger">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    `,
  );

  // 2. INITIALISATION
  const modalElement = document.getElementById("deleteModal");
  const saveModal = new bootstrap.Modal(modalElement);
  const executeBtn = document.getElementById("execute-action");
  let currentForm = null;

  // 3. ÉCOUTEURS D'ÉVÉNEMENTS

  // Clic sur les boutons Delete (on utilise la délégation d'événement)
  document.addEventListener("click", (e) => {
    if (e.target.matches('button[value="delete"]')) {
      e.preventDefault();
      currentForm = e.target.closest("form");
      saveModal.show();
    }
  });

  // Clic sur le bouton de confirmation "Delete" dans la modale
  executeBtn.addEventListener("click", async () => {
    if (!currentForm) return;

    try {
      const formData = new FormData(currentForm);

      const response = await fetch("category/delete_service", {
        // On appelle la nouvelle méthode
        method: "POST",
        body: new FormData(currentForm), // Plus besoin de headers compliqués
      });

      const data = await response.json();

      if (data.status === "success") {
        const section = currentForm.closest(".section");
        section.style.transition = "opacity 0.3s ease";
        section.style.opacity = "0";

        setTimeout(() => section.remove(), 300);
        saveModal.hide();
      } else {
        alert("Erreur : " + (data.message || "Une erreur est survenue"));
      }
    } catch (error) {
      console.error("Erreur réseau:", error);
      alert("Impossible de contacter le serveur.");
    }
  });
});