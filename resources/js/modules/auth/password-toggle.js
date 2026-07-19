export function initPasswordToggle() {
    document.querySelectorAll(".an-auth__toggle-password").forEach(button => {
    button.addEventListener("click", () => {
            const input = button.parentElement.querySelector("input");
            const icon = button.querySelector("i");

            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }

        });
    });
}