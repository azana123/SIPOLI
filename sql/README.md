# Database SIPOLI (PostgreSQL)

```
poliklinik_db/
├── install.sql                  # mirip main di java
├── 00_common.sql
├── tables/                      # 1 file = 1 entitas
│   ├── 01_users.sql
│   ├── 02_poli.sql
│   ├── 03_pasien.sql
│   ├── 04_admin.sql
│   ├── 05_dokter.sql
│   ├── 06_jadwal.sql
│   ├── 07_reservasi.sql
│   ├── 08_antrean.sql
│   ├── 09_obat.sql
│   ├── 10_pemeriksaan.sql
│   ├── 11_resep_obat.sql
│   └── 12_log_stok_obat.sql
├── features/                    # 1 file = 1 fitur (trigger/function)
│   ├── 01_kuota_reservasi.sql
│   ├── 02_nomor_antrean.sql
│   ├── 03_stok_obat.sql
│   └── 04_selesaikan_antrean.sql
└── seeds/
    └── 01_poli.sql
```
