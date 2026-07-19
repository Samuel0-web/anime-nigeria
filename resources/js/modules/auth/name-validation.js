import { nameRegex, setValid, setError, resetField } from "./helpers.js";

export function initNameValidation(form, updateButtons) {
    const fullname = form.querySelector("#name");
    if (!fullname) return;
    const field = fullname.closest(".an-auth__field");

    fullname.addEventListener("input", () => {
        const value = fullname.value.trim();

        if (value === "") {
            resetField(field);
            updateButtons?.();
            return;
        }

        if (value.length < 2) {
            setError(field, "Name is too short.");
            updateButtons?.();
            return;
        }

        if (!nameRegex.test(value)) {
            setError(
                field,
                "Only letters, spaces, apostrophes and hyphens are allowed."
            );
            updateButtons?.();
            return;
        }

        setValid(field);
        updateButtons?.();
    });
}