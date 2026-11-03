initShowPassword();
initDataMasterCard();

function initShowPassword() {
  const eye = document.getElementById("eye");
  if (!eye) {
    return;
  }
  const icon = eye.querySelector("i");
  const password = document.getElementById("form-password");

  eye.addEventListener("click", () => {
    const isText = password.type === "text";

    password.type = isText ? "password" : "text";
    icon.classList.toggle("fa-eye", isText);
    icon.classList.toggle("fa-eye-slash", !isText);
  });
}

function initDataMasterCard() {
  const radioButtons = document.querySelectorAll('input[type="radio"]');

  if (!radioButtons) {
    return;
  }

  radioButtons.forEach(radio => {
    radio.addEventListener("change", function (e) {
      const selectedId = e.target.id;
      
      document.querySelectorAll(".target-card").forEach(card => {
        card.classList.add("hidden");
      });
      
      // 3. Find and show the specific card that matches the selected ID
      // Assuming your target cards have IDs like "card-pasien"
      const cardToShow = document.getElementById(`card-daftar-${selectedId}`);
      console.log(cardToShow);
      if (cardToShow) {
        cardToShow.classList.remove("hidden");
      }
    });
  });
}
