-- =====================================================================
-- FITUR   : Job: kirim konfirmasi H-1 & batalkan yang tidak konfirmasi
-- URUTAN  : features/23
-- ISI     : fn_kirim_konfirmasi_h1, fn_batalkan_tidak_konfirmasi
-- DEPEND  : tables/03_pasien, 06_jadwal, 08_reservasi, 15_inbox
-- =====================================================================

-- A. Kirim permintaan konfirmasi ke inbox pasien untuk jadwal BESOK
CREATE OR REPLACE FUNCTION fn_kirim_konfirmasi_h1(p_tanggal DATE DEFAULT fn_today())
RETURNS INT AS $$
DECLARE
    v_n INT;
BEGIN
    WITH target AS (
        UPDATE reservasi r
           SET konfirmasi_diminta_at = NOW()
          FROM jadwal j
         WHERE j.id = r.jadwal_id
           AND j.tanggal = p_tanggal + 1
           AND r.status = 'menunggu'
           AND r.konfirmasi_diminta_at IS NULL
        RETURNING r.id, r.pasien_id, r.no_reservasi, j.tanggal, j.jam_mulai
    ), notif AS (
        INSERT INTO inbox (user_id, tipe, judul, pesan, ref_tipe, ref_id, butuh_aksi)
        SELECT p.user_id, 'konfirmasi_kehadiran', 'Konfirmasi kehadiran',
               format('Reservasi %s untuk besok (%s, pukul %s). Mohon konfirmasi kehadiran Anda atau jadwalkan ulang.',
                      t.no_reservasi, to_char(t.tanggal, 'DD-MM-YYYY'), to_char(t.jam_mulai, 'HH24:MI')),
               'reservasi', t.id, TRUE
          FROM target t
          JOIN pasien p ON p.id = t.pasien_id
         WHERE p.user_id IS NOT NULL
        RETURNING 1
    )
    SELECT COUNT(*) INTO v_n FROM target;
    RETURN v_n;
END;
$$ LANGUAGE plpgsql;

-- B. Batalkan reservasi hari ini yang sudah diminta konfirmasi tapi tidak dijawab
--    ASUMSI: tidak konfirmasi = batal, baik sudah reschedule maupun belum.
CREATE OR REPLACE FUNCTION fn_batalkan_tidak_konfirmasi(p_tanggal DATE DEFAULT fn_today())
RETURNS INT AS $$
DECLARE
    v_n INT;
BEGIN
    WITH batal AS (
        UPDATE reservasi r
           SET status = 'dibatalkan', alasan_batal = 'tidak_konfirmasi'
          FROM jadwal j
         WHERE j.id = r.jadwal_id
           AND j.tanggal = p_tanggal
           AND r.status = 'menunggu'
           AND r.konfirmasi_diminta_at IS NOT NULL
        RETURNING r.id, r.pasien_id, r.no_reservasi
    ), notif AS (
        INSERT INTO inbox (user_id, tipe, judul, pesan, ref_tipe, ref_id)
        SELECT p.user_id, 'info', 'Reservasi dibatalkan otomatis',
               format('Reservasi %s dibatalkan karena tidak dikonfirmasi.', b.no_reservasi),
               'reservasi', b.id
          FROM batal b
          JOIN pasien p ON p.id = b.pasien_id
         WHERE p.user_id IS NOT NULL
        RETURNING 1
    )
    SELECT COUNT(*) INTO v_n FROM batal;
    RETURN v_n;
END;
$$ LANGUAGE plpgsql;
