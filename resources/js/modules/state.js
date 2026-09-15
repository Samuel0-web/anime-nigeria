export function initFilledState(root = document) {
    const inputs = root.querySelectorAll(".an-auth__input");

    const sync = (input) => {
        const field = input.closest(".an-auth__field");
        if (!field) return;
        field.classList.toggle("is-filled", input.value.trim() !== "");
    };

    inputs.forEach((input) => {
        sync(input);

        ["input", "change", "blur"].forEach((evt) =>
            input.addEventListener(evt, () => sync(input))
        );
    });

    // Browsers autofill after load, sometimes without firing any event.
    window.addEventListener("load", () => inputs.forEach(sync));
    setTimeout(() => inputs.forEach(sync), 350);
}