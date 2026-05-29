// Validation Synchrone (Locale - IMMEDIATE)
const checkLengthLocally = (val) => {
    if (val.length < categoryConfig.min || val.length > categoryConfig.max) {
        return `La catégorie doit contenir entre ${categoryConfig.min} et ${categoryConfig.max} lettres`;
    }
    return null;
};

// Validation Asynchrone (Appel serveur)
const validateCategory = async (inputElement) => {
    const form = inputElement.closest('form');
    const errorSpan = form.querySelector('.error-message');
    const val = inputElement.value.trim();

    // 1. Reset visuel immédiat
    errorSpan.textContent = '';
    errorSpan.style.display = 'none';
    inputElement.style.border = '';

    // 2. Validation Locale (Immédiate)
    const localError = checkLengthLocally(val);
    if (localError) {
        errorSpan.textContent = localError;
        errorSpan.style.display = 'block';
        inputElement.style.border = '2px solid red';
        return; 
    }

    // 3. Validation Serveur (Unicité)
    try {
        const response = await fetch('category/validate_categories_service', {
            method: 'POST',
            body: new FormData(form)
        });
        const data = await response.json();
        
        if (data.errors && Object.keys(data.errors).length > 0) {
            errorSpan.textContent = Object.values(data.errors)[0];
            errorSpan.style.display = 'block';
            inputElement.style.border = '2px solid red';
        }
    } catch (err) {
        console.error("Erreur serveur :", err);
    }
};

// Initialisation simplifiée
document.addEventListener("DOMContentLoaded", () => {
    document.body.addEventListener("input", (e) => {
        if (e.target.matches(".input, .new_category")) {
            e.target.classList.add("touched");
            // Appel direct, sans debounce
            validateCategory(e.target);
        }
    });
});