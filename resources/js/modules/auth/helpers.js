// ======================================================================
// HELPERS
// ======================================================================

export const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
export const nameRegex = /^[A-Za-zÀ-ÿ' -]{2,}$/;

export function setError(field, message) {
    field.classList.remove("is-valid");
    field.classList.add("is-invalid");

    const error = field.querySelector(".an-auth__error");

    if (error) {
        error.textContent = message;
    }
}

export function clearError(field) {
    field.classList.remove("is-invalid");

    const error = field.querySelector(".an-auth__error");

    if (error) {
        error.textContent = "";
    }
}

export function setValid(field) {
    field.classList.remove("is-invalid");
    field.classList.add("is-valid");

    const error = field.querySelector(".an-auth__error");

    if (error) {
        error.textContent = "";
    }
}

export function resetField(field) {
    field.classList.remove("is-valid", "is-invalid");

    const error = field.querySelector(".an-auth__error");

    if (error) {
        error.textContent = "";
    }
}