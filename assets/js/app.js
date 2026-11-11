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

function detailDataPenanganan(element = null) {
  const leftCard = document.getElementById("left-card-penanganan");
  const rightCard = document.getElementById("right-card-penanganan");

  if (leftCard && rightCard) {
    // 1. Ubah porsi lebar card kiri (dari 12 kolom menjadi 7 kolom)
    leftCard.classList.remove("lg:col-span-12");
    leftCard.classList.add("lg:col-span-5");

    // 2. Tampilkan card kanan
    rightCard.classList.remove("hidden");
    rightCard.classList.add("flex"); // Karena menggunakan flex-col, kita ubah hidden menjadi flex

    // 3. Update UI baris yang aktif (Highlighting)
    if (element) {
      // Reset semua baris ke warna default (putih)
      const allRows = document.querySelectorAll(".items-card");
      console.log(element);

      allRows.forEach(row => {
        row.classList.remove(
          "bg-primary-50",
          "border-primary-100",
          "border-s-4",
          "border-s-primary",
        );
        row.classList.add("bg-white", "border-gray-100");
      });

      // Set baris yang diklik menjadi aktif
      element.classList.remove(
        "bg-white",
        "border-gray-100",
        "hover:bg-gray-50",
      );
      element.classList.add(
        "bg-primary-50",
        "border-primary-100",
        "border-s-4",
        "border-s-primary",
      );
    }
  } else {
    return;
  }
}

function addResepObat() {
  const newResepContainer = document.getElementById("form-resep-obat");
  var cardCount = document.getElementsByClassName("card-obat").length;

  console.log(cardCount);
  

  if (newResepContainer) {
    newResepContainer.insertAdjacentHTML(
      "beforeend",
      generateCardResep(cardCount + 1),
    );
  } else {
    return;
  }
}

function generateCardResep(index) {
  return `
    <div class="card-obat mb-4">
                            <div class="p-5 bg-page border border-primary-100 rounded space-y-4">
                                <!-- Header Card Obat -->
                                <div class="flex items-center justify-between">
                                    <h4 class="text-base font-bold text-gray-900">Obat ` + index + `</h4>
                                    <span class="text-xs text-gray-500">Bentuk, satuan &amp; stok terisi dari katalog &bull; hanya baca</span>
                                </div>

                                <!-- Baris 1: Nama Obat (Search & Dropdown) + Readonly Stats -->
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    <!-- Select Nama Obat -->
                                    <div class="md:col-span-6">
                                        <label for="nama_obat_1" class="block mb-2 text-sm font-medium text-gray-700">Nama obat *</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 inset-0 flex items-center ps-3.5 pointer-events-none text-gray-400">
                                                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                                </svg>
                                            </div>
                                            <select id="nama_obat_1" name="nama_obat[]" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full ps-10 p-2.5 pe-10 appearance-none placeholder-gray-400" required>
                                                <option value="" disabled selected>Cari kode atau nama obat...</option>
                                                <option value="OBT-001">Paracetamol 500 mg (OBT-001)</option>
                                                <option value="OBT-002">Ambroxol 30 mg (OBT-002)</option>
                                                <option value="OBT-003">Cetirizine 10 mg (OBT-003)</option>
                                            </select>
                                            <div class="absolute inset-y-0 inset-e-0 flex items-center pe-3 pointer-events-none text-gray-400">
                                                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Readonly: Bentuk -->
                                    <div class="md:col-span-2">
                                        <label class="block mb-2 text-sm font-medium text-gray-700">Bentuk</label>
                                        <div class="p-2.5 bg-cyan-50/70 border border-cyan-100/60 text-center text-sm font-medium text-gray-500 rounded">
                                            &mdash;
                                        </div>
                                    </div>

                                    <!-- Readonly: Satuan -->
                                    <div class="md:col-span-2">
                                        <label class="block mb-2 text-sm font-medium text-gray-700">Satuan</label>
                                        <div class="p-2.5 bg-cyan-50/70 border border-cyan-100/60 text-center text-sm font-medium text-gray-500 rounded">
                                            &mdash;
                                        </div>
                                    </div>

                                    <!-- Readonly: Stok tersedia -->
                                    <div class="md:col-span-2">
                                        <label class="block mb-2 text-sm font-medium text-gray-700">Stok tersedia</label>
                                        <div class="p-2.5 bg-cyan-50/70 border border-cyan-100/60 text-center text-sm font-medium text-gray-500 rounded">
                                            &mdash;
                                        </div>
                                    </div>
                                </div>

                                <!-- Baris 2: Jumlah, Dosis, Aturan pakai -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="jumlah_1" class="block mb-2 text-sm font-medium text-gray-700">Jumlah *</label>
                                        <input type="text" id="jumlah_1" name="jumlah[]" placeholder="Masukkan jumlah" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400" required>
                                    </div>
                                    <div>
                                        <label for="dosis_1" class="block mb-2 text-sm font-medium text-gray-700">Dosis *</label>
                                        <input type="text" id="dosis_1" name="dosis[]" placeholder="Tuliskan dosis obat" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400" required>
                                    </div>
                                    <div>
                                        <label for="aturan_1" class="block mb-2 text-sm font-medium text-gray-700">Aturan pakai *</label>
                                        <input type="text" id="aturan_1" name="aturan[]" placeholder="Tuliskan aturan penggunaan obat" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400" required>
                                    </div>
                                </div>

                                <!-- Baris 3: Catatan Item -->
                                <div>
                                    <label for="catatan_1" class="block mb-2 text-sm font-medium text-gray-700">Catatan item (opsional)</label>
                                    <input type="text" id="catatan_1" name="catatan[]" placeholder="Tambahkan catatan khusus untuk obat ini..." class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400">
                                </div>
                            </div>
                        </div>
  `;
}

// function detailDataResep(element = null) {
//     const leftCard = document.getElementById('left-card-resep');
//     const rightCard = document.getElementById('right-card-resep');

//     if (leftCard && rightCard) {
//         // 1. Ubah porsi lebar card kiri (dari 12 kolom menjadi 7 kolom)
//         leftCard.classList.remove('lg:col-span-12');
//         leftCard.classList.add('lg:col-span-7');

//         // 2. Tampilkan card kanan
//         rightCard.classList.remove('hidden');
//         rightCard.classList.add('flex'); // Karena menggunakan flex-col, kita ubah hidden menjadi flex

//         // 3. Update UI baris yang aktif (Highlighting)
//         if (element) {
//             // Reset semua baris ke warna default (putih)
//             const allRows = document.querySelectorAll('tbody tr');
//             allRows.forEach(row => {
//                 row.classList.remove('bg-primary-50', 'border-primary-100', 'border-s-4' ,'border-s-primary');
//                 row.classList.add('bg-white', 'border-gray-100');
//             });

//             // Set baris yang diklik menjadi aktif
//             element.classList.remove('bg-white', 'border-gray-100', 'hover:bg-gray-50');
//             element.classList.add('bg-primary-50', 'border-primary-100', 'border-s-4' ,'border-s-primary');
//         }
//     } else {
//       return;
//     }
// }
