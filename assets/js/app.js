initShowPassword();

function initShowPassword() {
  const eye = document.getElementById("eye");
  const icon = eye.querySelector("i");
  const password = document.getElementById("form-password");
  if (!eye) {
    return;
  }

  eye.addEventListener("click", () => {
    const isText = password.type === "text";

    password.type = isText ? "password" : "text";
    icon.classList.toggle("fa-eye", isText);
    icon.classList.toggle("fa-eye-slash", !isText);
  });
}
