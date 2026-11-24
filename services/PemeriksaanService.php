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
        $tb        = api_float($in, 'tb') ?: null;
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
            $dokterId, $antreanId, $tensi, $suhu, $bb, $tb, $anamnesis, $diagnosa, $tindak, $resep
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

            // Bangun data klinis dalam JSONB sesuai poli
            $dataPemeriksaan = [
                'keluhan'         => $keluhan !== '' ? $keluhan : null,
                'diagnosa'        => $diagnosa !== '' ? $diagnosa : null,
                'catatan'         => $anamnesis !== '' ? $anamnesis : null,
                'tindak_lanjut'   => $tindak !== '' ? $tindak : null,
            ];
            // Vital signs hanya untuk poli umum (poli_id = 1)
            if ((int) $antrean['poli_id'] === 1) {
                if ($tensi !== null)    $dataPemeriksaan['tekanan_darah'] = $tensi;
                if ($suhu !== null)     $dataPemeriksaan['suhu'] = $suhu;
                if ($bb !== null)       $dataPemeriksaan['berat_badan'] = $bb;
                if ($tb !== null)       $dataPemeriksaan['tinggi_badan'] = $tb;
            }

            $stmtPem = $pdo->prepare("
                INSERT INTO pemeriksaan (
                    antrean_id, pasien_id, dokter_id, poli_id, tanggal,
                    data_pemeriksaan
                ) VALUES (
                    :antrean, :pasien, :dokter, :poli, :tanggal,
                    :data
                )
                ON CONFLICT (antrean_id) DO UPDATE
                   SET data_pemeriksaan = EXCLUDED.data_pemeriksaan
                RETURNING id
            ");
            $stmtPem->bindValue(':antrean', $antreanId, PDO::PARAM_INT);
            $stmtPem->bindValue(':pasien', (int) $antrean['pasien_id'], PDO::PARAM_INT);
            $stmtPem->bindValue(':dokter', $dokterId, PDO::PARAM_INT);
            $stmtPem->bindValue(':poli', (int) $antrean['poli_id'], PDO::PARAM_INT);
            $stmtPem->bindValue(':tanggal', $antrean['tanggal']);
            $stmtPem->bindValue(':data', json_encode($dataPemeriksaan, JSON_UNESCAPED_UNICODE));
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
        // Input bisa berisi array (banyak obat) maupun string (satu obat).
        // Samakan keduanya jadi array supaya satu resep pun tidak terlewat.
        $obatIds  = isset($in['obat_id']) ? (array) $in['obat_id'] : [];
        $jumlahs  = isset($in['jumlah']) ? (array) $in['jumlah'] : [];
        $dosis    = isset($in['dosis']) ? (array) $in['dosis'] : [];
        $aturan   = isset($in['aturan']) ? (array) $in['aturan'] : [];
        $catatan  = isset($in['catatan']) ? (array) $in['catatan'] : [];
        $hasil    = [];

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
