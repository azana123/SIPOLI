<?php
declare(strict_types=1);

final class QueryAdminPasienOffline {
    public static function poliWalkinList() {
        return db()->query("SELECT id, nama FROM poli WHERE butuh_reservasi = FALSE ORDER BY id ASC")->fetchAll();
    }
}
