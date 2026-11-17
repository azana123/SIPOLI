<?php
$apiBase = rtrim((string) getenv('APP_BASE_URL'), '/');
?>
<script>window.APP_ROOT = <?= json_encode($apiBase) ?>;</script>
<script defer src="<?= htmlspecialchars($apiBase . '/assets/js/api.js', ENT_QUOTES, 'UTF-8') ?>"></script>
