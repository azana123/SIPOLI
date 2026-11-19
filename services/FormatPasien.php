<?php
declare(strict_types=1);

final class FormatPasien {
    public static function labelStatus(string $status, int $reschedule): string {
        if ($reschedule > 0 && !in_array($status, ['dibatalkan', 'selesai'], true)) {
            return 'Reschedule';
        }
        return [
            'menunggu' => 'Menunggu',
            'dikonfirmasi' => 'Terkonfirmasi',
            'hadir' => 'Hadir',
            'tidak_hadir' => 'Tidak Hadir',
            'dibatalkan' => 'Dibatalkan',
            'selesai' => 'Selesai',
        ][$status] ?? $status;
    }

    public static function warnaStatus(string $status, int $reschedule): string {
        if ($reschedule > 0 && !in_array($status, ['dibatalkan', 'selesai'], true)) {
            return 'bg-blue-100 text-blue-700';
        }
        return match ($status) {
            'menunggu' => 'bg-orange-100 text-orange-700',
            'dikonfirmasi', 'hadir' => 'bg-green-100 text-green-700',
            'selesai' => 'bg-gray-200 text-gray-700',
            'tidak_hadir', 'dibatalkan' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public static function tanggalPendek(string $tanggal): string {
        $bulan = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
        $hari = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
        $ts = strtotime($tanggal);
        return $hari[(int) date('N', $ts)] . ', ' . (int) date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)];
    }

    public static function tanggalPanjang(string $tanggal): string {
        $bulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $hari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $ts = strtotime($tanggal);
        return $hari[(int) date('N', $ts)] . ', ' . (int) date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }

    public static function jam($jam): string {
        $jam = (string) $jam;
        return strlen($jam) >= 5 ? substr($jam, 0, 5) : $jam;
    }

    public static function waktuRelatif($createdAt): string {
        $ts = strtotime((string) $createdAt);
        $selisih = time() - $ts;
        if ($selisih < 60) return 'Baru saja';
        if ($selisih < 3600) return ((int) floor($selisih / 60)) . ' menit lalu';
        if ($selisih < 86400) return ((int) floor($selisih / 3600)) . ' jam lalu';
        return ((int) floor($selisih / 86400)) . ' hari lalu';
    }
}
