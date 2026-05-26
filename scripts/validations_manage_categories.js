/**
 * Utilisation d'une fonction utilitaire de Debounce moderne
 */
const debounce = (fn, delay = 400) => {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn(...args), delay);
  };
};

/**
 * Logique de validation asynchrone
 */
const validateCategory = async (inputElement) => {
    const form = inputElement.closest('form');
    const formData = new FormData(form);
    const errorSpan = form.querySelector('.error-message');
    const addBtn = form.querySelector('button[value="add"]');
    const editBtn = form.querySelector('button[value="save"]');

    // 1. Nettoyage immédiat : dès qu'on retouche le champ, on reset l'état
    errorSpan.textContent = '';
    errorSpan.style.display = 'none';
    inputElement.style.border = '';
    if (addBtn) addBtn.disabled = false;
    if (editBtn) editBtn.disabled = false;

    try {
        const response = await fetch('category/validate_categories_service', {
            method: 'POST',
            body: formData
        });

        // Lecture brute pour déboguer si le JSON échoue
        const text = await response.text();
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("JSON invalide :", text);
            return;
        }

        // 2. Gestion des erreurs renvoyées par le serveur
        const errorMessage = data.errors?.category_name || data.errors?.name;

        if (errorMessage) {
            errorSpan.textContent = errorMessage;
            errorSpan.style.display = 'block';
            inputElement.style.border = '2px solid red';
            if (editBtn) editBtn.disabled = true;
            if (addBtn) addBtn.disabled = true;
        }

    } catch (err) {
        console.error("Erreur de communication :", err);
    }
};

// Initialisation
document.addEventListener("DOMContentLoaded", () => {
  const debouncedValidate = debounce(validateCategory);

  // Délégation d'événement sur le container parent pour gérer les nouveaux éléments
  document.body.addEventListener("input", (e) => {
    if (e.target.matches(".input, .new_category")) {
      e.target.classList.add("touched");
      debouncedValidate(e.target);
    }
  });
});
