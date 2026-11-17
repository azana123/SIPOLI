<?php
declare(strict_types=1);

final class PemeriksaanService {
    public static function draft(array $in): array {
        $antreanId = api_int($in, 'antrean_id');
        if ($antreanId <= 0) {
            throw new ApiException('Antrean tidak ditemukan.');
        }

        $_SESSION['draft_pemeriksaan'][$antreanId] = [
            'tensi'             => api_str($in, 'tensi'),
            'suhu'              => api_str($in, 'suhu'),
            'bb'                => api_str($in, 'bb'),
            'hasil_pemeriksaan' => api_str($in, 'hasil_pemeriksaan'),
            'diagnosis'         => api_str($in, 'diagnosis'),
            'saran_medis'       => api_str($in, 'saran_medis'),
        ];

        return ['antrean_id' => $antreanId, 'tersimpan' => true];
    }

    public static function selesai(array $in): array {
        $dokterId  = profil_id_sesi('dokter');
        $antreanId = api_int($in, 'antrean_id');
        $tensi     = api_str($in, 'tensi') ?: null;
        $suhu      = api_float($in, 'suhu') ?: null;
        $bb        = api_float($in, 'bb') ?: null;
        $anamnesis = api_str($in, 'hasil_pemeriksaan');
        $diagnosa  = api_str($in, 'diagnosis');
        $tindak    = api_str($in, 'saran_medis') ?: null;

        if ($dokterId <= 0 || $antreanId <= 0) {
            throw new ApiException('Data dokter atau antrean tidak valid.');
        }
        if ($diagnosa === '') {
            throw new ApiException('Diagnosa wajib diisi.');
        }
        if ($suhu !== null && ($suhu < 30 || $suhu > 45)) {
            throw new ApiException('Suhu tubuh harus antara 30 sampai 45 derajat.');
        }

        $resep = self::resepValid($in);

        $pemeriksaanId = api_transaction(static function (PDO $pdo) use (
            $dokterId, $antreanId, $tensi, $suhu, $bb, $anamnesis, $diagnosa, $tindak, $resep
        ): int {
            $stmt = $pdo->prepare("
                SELECT a.id, a.pasien_id, a.poli_id, a.tanggal, r.keluhan AS keluhan_rsv
                FROM antrean a
                LEFT JOIN reservasi r ON r.id = a.reservasi_id
                WHERE a.id = :id FOR UPDATE OF a
            ");
            $stmt->execute([':id' => $antreanId]);
            $antrean = $stmt->fetch();
            if (!$antrean) {
                throw new ApiException('Data antrean pasien tidak ditemukan.');
            }

            $pdo->prepare("
                UPDATE antrean
                   SET status = CASE WHEN status = 'menunggu' THEN 'diperiksa' ELSE status END,
                       dokter_id = COALESCE(dokter_id, :dokter),
                       waktu_mulai = COALESCE(waktu_mulai, NOW())
                 WHERE id = :id
            ")->execute([':dokter' => $dokterId, ':id' => $antreanId]);

            $keluhan = $antrean['keluhan_rsv'] ?: $anamnesis;
            $stmtPem = $pdo->prepare("
                INSERT INTO pemeriksaan (
                    antrean_id, pasien_id, dokter_id, poli_id, tanggal,
                    keluhan, tekanan_darah, suhu, berat_badan, diagnosa, catatan, tindak_lanjut
                ) VALUES (
                    :antrean, :pasien, :dokter, :poli, :tanggal,
                    :keluhan, :tensi, :suhu, :bb, :diagnosa, :catatan, :tindak
                )
                ON CONFLICT (antrean_id) DO UPDATE
                   SET tekanan_darah = EXCLUDED.tekanan_darah,
                       suhu = EXCLUDED.suhu,
                       berat_badan = EXCLUDED.berat_badan,
                       diagnosa = EXCLUDED.diagnosa,
                       catatan = EXCLUDED.catatan,
                       tindak_lanjut = EXCLUDED.tindak_lanjut
                RETURNING id
            ");
            $stmtPem->bindValue(':antrean', $antreanId, PDO::PARAM_INT);
            $stmtPem->bindValue(':pasien', (int) $antrean['pasien_id'], PDO::PARAM_INT);
            $stmtPem->bindValue(':dokter', $dokterId, PDO::PARAM_INT);
            $stmtPem->bindValue(':poli', (int) $antrean['poli_id'], PDO::PARAM_INT);
            $stmtPem->bindValue(':tanggal', $antrean['tanggal']);
            $stmtPem->bindValue(':keluhan', $keluhan !== '' ? $keluhan : null, $keluhan !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtPem->bindValue(':tensi', $tensi, $tensi === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtPem->bindValue(':suhu', $suhu, $suhu === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtPem->bindValue(':bb', $bb, $bb === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtPem->bindValue(':diagnosa', $diagnosa);
            $stmtPem->bindValue(':catatan', $anamnesis);
            $stmtPem->bindValue(':tindak', $tindak, $tindak === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtPem->execute();
            $id = (int) $stmtPem->fetchColumn();
            if ($id <= 0) {
                throw new ApiException('Gagal memperoleh ID rekam medis.');
            }

            if ($resep !== []) {
                $stmtResep = $pdo->prepare("
                    INSERT INTO resep_obat (pemeriksaan_id, obat_id, jumlah, aturan_pakai)
                    VALUES (:pem, :obat, :jumlah, :aturan)
                    ON CONFLICT (pemeriksaan_id, obat_id) DO NOTHING
                ");
                foreach ($resep as $item) {
                    $stmtResep->execute([
                        ':pem'    => $id,
                        ':obat'   => $item['obat_id'],
                        ':jumlah' => $item['jumlah'],
                        ':aturan' => $item['aturan'],
                    ]);
                }
                $pdo->prepare("
                    INSERT INTO permintaan_obat (pemeriksaan_id, status, catatan)
                    VALUES (:pem, 'menunggu', :cat)
                    ON CONFLICT (pemeriksaan_id) DO NOTHING
                ")->execute([':pem' => $id, ':cat' => 'Resep dari pemeriksaan #' . $id]);
            }

            return $id;
        });

        unset($_SESSION['draft_pemeriksaan'][$antreanId]);

        return ['pemeriksaan_id' => $pemeriksaanId, 'antrean_id' => $antreanId];
    }

    // Gabungkan array-array obat dari form jadi satu daftar valid
    private static function resepValid(array $in): array {
        $obatIds  = $in['obat_id'] ?? [];
        $jumlahs  = $in['jumlah'] ?? [];
        $dosis    = $in['dosis'] ?? [];
        $aturan   = $in['aturan'] ?? [];
        $catatan  = $in['catatan'] ?? [];
        $hasil    = [];

        if (!is_array($obatIds)) {
            return $hasil;
        }
        foreach ($obatIds as $i => $obatRaw) {
            $obatId = (int) $obatRaw;
            $jumlah = (int) ($jumlahs[$i] ?? 0);
            if ($obatId <= 0 || $jumlah <= 0) {
                continue;
            }
            $teks = trim(
                api_str($dosis, (string) $i) .
                (api_str($aturan, (string) $i) !== '' ? ' - ' . api_str($aturan, (string) $i) : '') .
                (api_str($catatan, (string) $i) !== '' ? ' (' . api_str($catatan, (string) $i) . ')' : '')
            );
            $teks = mb_substr($teks, 0, 100);

            if (isset($hasil[$obatId])) {
                $hasil[$obatId]['jumlah'] += $jumlah;
            } else {
                $hasil[$obatId] = [
                    'obat_id' => $obatId,
                    'jumlah'  => $jumlah,
                    'aturan'  => $teks !== '' ? $teks : null,
                ];
            }
        }
        return array_values($hasil);
    }
}
