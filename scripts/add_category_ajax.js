document.addEventListener("DOMContentLoaded", () => {
    // On cible le formulaire d'ajout (celui qui est en bas de page)
    const addForm = document.querySelector('.section-ajout form');
    const inputField = addForm.querySelector('input[name="new_category"]');
    const errorSpan = addForm.querySelector('.error-message');

    addForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // On bloque le rechargement classique

        // Reset des erreurs
        errorSpan.textContent = '';
        errorSpan.style.display = 'none';

        try {
            const response = await fetch('category/add_service', {
                method: 'POST',
                body: new FormData(addForm)
            });

            const data = await response.json();

            if (data.status === 'success') {
                // 1. On crée dynamiquement le HTML de la nouvelle section
                const newSection = document.createElement('div');
                newSection.className = 'section';
                newSection.dataset.id = data.id;
                
                newSection.innerHTML = `
                    <form method="POST" action="category" class="form-box">
                        <input type="hidden" name="category_id" value="${data.id}">
                        <span class="drag-handle">≡</span>
                        <input type="text" name="name" class="input only-no-js" value="${data.name}" required>
                        <span class="only-js">${data.name}</span>
                        <span class="only-js">(0)</span>
                        <button type="submit" name="action" class="edit" value="edit">Edit</button>
                        <button type="submit" name="action" value="delete">Delete</button>
                    </form>
                `;

                // 2. On l'ajoute au conteneur principal
                document.querySelector('.container').appendChild(newSection);

                // 3. Reset du champ d'ajout
                inputField.value = '';
                
            } else {
                // Gestion des erreurs serveurs
                errorSpan.textContent = data.message || "Erreur lors de l'ajout.";
                errorSpan.style.display = 'block';
            }
        } catch (error) {
            console.error("Erreur:", error);
        }
    });
});