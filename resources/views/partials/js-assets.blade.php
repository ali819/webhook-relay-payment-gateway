{{-- Aset JS bersama + helper global. Dipakai layout panel maupun halaman auth. --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.querySelectorAll('input, select, textarea').forEach(el => {
    el.setAttribute('autocomplete', 'off');
});

// ---- SweetAlert ----
window.notify = function (message, icon = 'success') {
    Swal.fire({
        toast: true, position: 'top-end', icon, title: message,
        showConfirmButton: false, timer: 2500, timerProgressBar: true,
    });
};

window.confirmAction = function (opts) {
    return Swal.fire({
        title: opts.title || 'Yakin?',
        text: opts.text || 'Tindakan ini tidak bisa dibatalkan.',
        icon: opts.icon || 'warning',
        showCancelButton: true,
        confirmButtonText: opts.btn || 'Ya, lanjutkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#212529',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
    }).then(r => r.isConfirmed);
};

// Konfirmasi untuk form non-AJAX: <form data-confirm data-confirm-title="...">
document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        confirmAction({
            title: this.dataset.confirmTitle,
            text:  this.dataset.confirmText,
            btn:   this.dataset.confirmBtn,
            icon:  this.dataset.confirmIcon,
        }).then(ok => { if (ok) this.submit(); });
    });
});

// ---- Tombol mata pada field password: <button data-toggle-password="#id"> ----
$(document).on('click', '[data-toggle-password]', function () {
    const input = $($(this).data('toggle-password'));
    const show  = input.attr('type') === 'password';

    input.attr('type', show ? 'text' : 'password');
    $(this).find('i').toggleClass('bi-eye', !show).toggleClass('bi-eye-slash', show);
    $(this).attr('title', show ? 'Sembunyikan password' : 'Tampilkan password');
});

// ---- Helper AJAX (hanya di halaman yang punya token CSRF) ----
const csrfMeta = document.querySelector('meta[name="csrf-token"]');

if (csrfMeta) {
    window.CSRF_TOKEN = csrfMeta.content;

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, 'Accept': 'application/json' },
    });
}

window.escapeHtml = function (v) {
    if (v === null || v === undefined || v === '') return '-';
    return $('<div>').text(v).html();
};

window.providerBadge = function (p) {
    const label = p ? p.charAt(0).toUpperCase() + p.slice(1) : '-';
    return '<span class="badge badge-' + (p || 'unknown') + '">' + label + '</span>';
};

// Bahasa DataTables (dipakai semua tabel)
window.DT_LANG = {
    processing:   'Memuat...',
    search:       'Cari:',
    lengthMenu:   'Tampilkan _MENU_ baris',
    info:         'Menampilkan _START_-_END_ dari _TOTAL_ data',
    infoEmpty:    'Tidak ada data',
    infoFiltered: '(difilter dari _MAX_ total)',
    zeroRecords:  'Tidak ada data yang cocok',
    emptyTable:   'Belum ada data',
    paginate:     { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' },
};
</script>
