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

function detailDataResep(element = null) {
    const leftCard = document.getElementById('left-card-resep');
    const rightCard = document.getElementById('right-card-resep');

    if (leftCard && rightCard) {
        // 1. Ubah porsi lebar card kiri (dari 12 kolom menjadi 7 kolom)
        leftCard.classList.remove('lg:col-span-12');
        leftCard.classList.add('lg:col-span-7'); 

        // 2. Tampilkan card kanan
        rightCard.classList.remove('hidden');
        rightCard.classList.add('flex'); // Karena menggunakan flex-col, kita ubah hidden menjadi flex

        // 3. Update UI baris yang aktif (Highlighting)
        if (element) {
            // Reset semua baris ke warna default (putih)
            const allRows = document.querySelectorAll('tbody tr');
            allRows.forEach(row => {
                row.classList.remove('bg-primary-50', 'border-primary-100', 'border-s-4' ,'border-s-primary');
                row.classList.add('bg-white', 'border-gray-100');
            });

            // Set baris yang diklik menjadi aktif
            element.classList.remove('bg-white', 'border-gray-100', 'hover:bg-gray-50');
            element.classList.add('bg-primary-50', 'border-primary-100', 'border-s-4' ,'border-s-primary');
        }
    } else {
      return;
    }
}
