(function () {
    'use strict';

    const root = window.APP_ROOT || '';
    let tokenCache = null;

    async function apiCsrf() {
        if (tokenCache) return tokenCache;
        const res = await fetch(root + '/api/csrf.php', { credentials: 'same-origin' });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Gagal mengambil token CSRF.');
        tokenCache = json.data.csrf_token;
        return tokenCache;
    }

    function apiToast(message, ok = true) {
        document.querySelectorAll('.api-toast').forEach((el) => el.remove());
        const box = document.createElement('div');
        box.className = 'api-toast fixed top-0 inset-x-0 z-[9999] flex items-center gap-2 px-4 md:px-8 py-3 text-sm border-b shadow-sm ' +
            (ok
                ? 'bg-success-soft text-fg-success-strong border-success'
                : 'bg-danger-soft text-fg-danger border-danger');

        const icon = document.createElement('i');
        icon.className = ok ? 'fas fa-check-circle shrink-0' : 'fas fa-exclamation-circle shrink-0';
        const teks = document.createElement('span');
        teks.textContent = message || (ok ? 'Berhasil.' : 'Terjadi kesalahan.');

        box.append(icon, teks);
        document.body.appendChild(box);
        setTimeout(() => box.remove(), 4000);
    }

    async function apiFetch(endpoint, payload = {}, method = 'POST') {
        const token = await apiCsrf();
        const res = await fetch(root + endpoint, {
            method: method,
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token,
            },
            body: method === 'GET' ? undefined : JSON.stringify(payload),
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Permintaan gagal.');
        return json;
    }

    const BOOLEAN_FIELDS = [
        'dipublikasi', 'butuh_reservasi', 'is_super', 'tanpa_batas',
        'hadir', 'new_state', 'aksi_selesai',
    ];

    function payloadDariForm(form, submitter) {
        const fd = new FormData(form);
        if (submitter && submitter.name) {
            fd.append(submitter.name, submitter.value);
        }
        const data = {};
        fd.forEach((value, key) => {
            const berulang = key.endsWith('[]');
            const kunci = berulang ? key.slice(0, -2) : key;
            if (berulang) {
                if (!Array.isArray(data[kunci])) data[kunci] = [];
                data[kunci].push(value);
            } else if (Object.prototype.hasOwnProperty.call(data, kunci)) {
                if (!Array.isArray(data[kunci])) data[kunci] = [data[kunci]];
                data[kunci].push(value);
            } else {
                data[kunci] = value;
            }
        });
        BOOLEAN_FIELDS.forEach((name) => {
            if (form.querySelector('[name="' + name + '"]') && !(name in data)) {
                data[name] = false;
            }
        });
        if (form.dataset.apiAction) data.action = form.dataset.apiAction;
        return data;
    }

    function sibuk(el, aktif) {
        if (!el) return;
        if (aktif) {
            el.dataset.apiLabelAsli = el.innerHTML;
            el.setAttribute('disabled', 'disabled');
            el.innerHTML = 'Memproses…';
        } else {
            el.removeAttribute('disabled');
            if (el.dataset.apiLabelAsli !== undefined) el.innerHTML = el.dataset.apiLabelAsli;
        }
    }

    function arahkan(tujuan, data) {
        if (!tujuan) {
            window.location.reload();
            return;
        }
        const hasil = tujuan.replace(/\{(\w+)\}/g, (_, kunci) => {
            const nilai = data && data[kunci] !== undefined ? data[kunci] : '';
            return encodeURIComponent(String(nilai));
        });
        window.location.href = root + hasil;
    }

    function sesudahSukses(form, json) {
        apiToast(json.message || 'Berhasil.', true);
        setTimeout(() => arahkan(form.dataset.apiRedirect, json.data), 500);
    }

    document.addEventListener('submit', async function (e) {
        const form = e.target;
        if (!form.matches || !form.matches('[data-api-endpoint]')) return;
        if (e.defaultPrevented) return;
        e.preventDefault();
        const tombol = e.submitter || form.querySelector('[type="submit"]');
        sibuk(tombol, true);
        try {
            const json = await apiFetch(form.dataset.apiEndpoint, payloadDariForm(form, e.submitter));
            sesudahSukses(form, json);
        } catch (err) {
            apiToast(err.message, false);
            sibuk(tombol, false);
        }
    });

    document.addEventListener('click', async function (e) {
        const el = e.target.closest('[data-api-click]');
        if (!el) return;
        e.preventDefault();
        let payload = {};
        try {
            payload = JSON.parse(el.dataset.apiPayload || '{}');
        } catch (err) {
            apiToast('Data aksi tidak valid.', false);
            return;
        }
        sibuk(el, true);
        try {
            const json = await apiFetch(el.dataset.apiEndpoint, payload);
            apiToast(json.message || 'Berhasil.', true);
            setTimeout(() => arahkan(el.dataset.apiRedirect, json.data), 500);
        } catch (err) {
            apiToast(err.message, false);
            sibuk(el, false);
        }
    });

    window.apiFetch = apiFetch;
    window.apiToast = apiToast;
})();
