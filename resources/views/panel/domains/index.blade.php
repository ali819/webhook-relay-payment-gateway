@extends('layouts.app')
@section('title', 'Domains')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
        <h5 class="fw-semibold mb-0">Domains</h5>
        <p class="text-muted small mb-0">Kelola domain &amp; endpoint relay</p>
    </div>
    <button class="btn btn-dark" id="btn-create">
        <i class="bi bi-plus-lg me-1"></i>Tambah Domain
    </button>
</div>

{{-- Relay URL info --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="text-danger">* Catatan : </span>
            <span class="small text-muted">Webhook URL <span class="fst-italic">(daftarkan ini di dashboard Midtrans/Xendit/DOKU):</span></span>
            <code class="bg-light px-3 py-1 rounded small" id="relay-url">{{ route('handleApi') }}</code>
            <button class="btn btn-outline-secondary py-0 px-2" id="copy-relay-btn" title="Copy relay URL">
                <i class="bi bi-copy" style="font-size:12px"></i>
            </button>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Provider</label>
                <select id="f-provider" class="form-select">
                    <option value="">Semua</option>
                    @foreach(\App\Models\Domain::PROVIDERS as $p)
                        <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Status</label>
                <select id="f-active" class="form-select">
                    <option value="">Semua</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-outline-secondary" id="btn-reset">Reset</button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="tbl-domains">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th class="d-none d-md-table-cell">Alias</th>
                        <th>Provider</th>
                        <th class="d-none d-lg-table-cell">Keterangan</th>
                        <th class="d-none d-md-table-cell">Target URL</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Modal form (create + edit) --}}
<div class="modal fade" id="domainModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form id="domain-form" autocomplete="off">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-semibold" id="domainModalTitle">Tambah Domain</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="f-id">

                    {{-- Keterangan gaya FAQ: default tertutup, dibuka per field --}}
                    <div class="mb-3 d-none" id="alias-box">
                        <label class="form-label fw-medium d-flex align-items-center gap-2">
                            Alias invoice
                            <a class="ms-auto small text-decoration-none text-muted" data-bs-toggle="collapse"
                               href="#help-alias" role="button" aria-expanded="false" aria-controls="help-alias">
                                <i class="bi bi-question-circle"></i> Apa ini?
                            </a>
                        </label>
                        <div class="collapse" id="help-alias">
                            <div class="border rounded bg-light-subtle p-2 mb-2 small text-muted">
                                Cara <strong>opsional</strong> mengenali tujuan tanpa metadata: tempelkan alias ini
                                di akhir nomor invoice, dipisah tanda hubung — mis. <code>INV-08314-<span id="alias-sample">XXX</span></code>.
                                Relay hanya membacanya kalau polanya persis begitu.
                                Kalau <code>custom_field1</code> / <code>metadata.domain</code> /
                                <code>additional_info.domain</code> sudah terkirim, alias tidak diperlukan.
                            </div>
                        </div>
                        <div class="input-group">
                            <input type="text" id="fm-alias" class="form-control font-monospace" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="btn-copy-alias" title="Salin alias">
                                <i class="bi bi-copy"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted">Digenerate otomatis dan tidak bisa diubah.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium d-flex align-items-center gap-2">
                            Provider <span class="text-danger">*</span>
                            <a class="ms-auto small text-decoration-none text-muted" data-bs-toggle="collapse"
                               href="#help-provider" role="button" aria-expanded="false" aria-controls="help-provider">
                                <i class="bi bi-question-circle"></i> Apa ini?
                            </a>
                        </label>
                        <div class="collapse" id="help-provider">
                            <div class="border rounded bg-light-subtle p-2 mb-2 small text-muted">
                                Payment gateway yang mengirim webhook untuk aplikasi ini. Satu domain boleh
                                didaftarkan ke lebih dari satu provider — buat entri terpisah per provider.
                            </div>
                        </div>
                        <select name="provider" id="fm-provider" class="form-select">
                            <option value="">-- Pilih provider --</option>
                            @foreach(\App\Models\Domain::PROVIDERS as $p)
                                <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" data-error="provider"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium d-flex align-items-center gap-2">
                            Target URL <span class="text-danger">*</span>
                            <a class="ms-auto small text-decoration-none text-muted" data-bs-toggle="collapse"
                               href="#help-target" role="button" aria-expanded="false" aria-controls="help-target">
                                <i class="bi bi-question-circle"></i> Apa ini?
                            </a>
                        </label>
                        <div class="collapse" id="help-target">
                            <div class="border rounded bg-light-subtle p-2 mb-2 small text-muted">
                                Yang didaftarkan di sini adalah <strong>endpoint aplikasi kamu</strong> — tempat
                                relay meneruskan notifikasi pembayaran, bukan URL relay. URL relay hanya satu
                                dan sudah tampil di halaman Domains.
                                <span class="d-block mt-2">
                                    Isi URL lengkap (pakai <code>https://</code>) endpoint yang biasanya kamu
                                    daftarkan langsung ke dashboard PG. Harus bisa diakses publik dan membalas HTTP 2xx.
                                </span>
                                <span class="d-block mt-2">
                                    Host dari URL ini otomatis dipakai sebagai <strong>domain identifier</strong>,
                                    yaitu nilai yang kamu kirim di <code>custom_field1</code> /
                                    <code>metadata.domain</code> / <code>additional_info.domain</code> saat membuat transaksi.
                                </span>
                            </div>
                        </div>
                        <input type="url" name="target_url" id="fm-target_url" class="form-control"
                               placeholder="https://toko-a.com/api/payment/callback">
                        <div id="domain-preview" class="form-text"></div>
                        <div class="invalid-feedback" data-error="target_url"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium d-flex align-items-center gap-2">
                            Keterangan
                            <a class="ms-auto small text-decoration-none text-muted" data-bs-toggle="collapse"
                               href="#help-notes" role="button" aria-expanded="false" aria-controls="help-notes">
                                <i class="bi bi-question-circle"></i> Apa ini?
                            </a>
                        </label>
                        <div class="collapse" id="help-notes">
                            <div class="border rounded bg-light-subtle p-2 mb-2 small text-muted">
                                Opsional — label bebas untuk membedakan entri di daftar, mis. Production /
                                Sandbox. Tidak berpengaruh ke jalannya relay.
                            </div>
                        </div>
                        <input type="text" name="notes" id="fm-notes" class="form-control"
                               placeholder="Contoh: Production, Local Test, Sandbox, dll">
                        <div class="invalid-feedback" data-error="notes"></div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div class="form-check mb-0">
                            <input type="checkbox" class="form-check-input" name="is_active" id="fm-is_active" checked>
                            <label class="form-check-label" for="fm-is_active">Aktif</label>
                        </div>
                        <a class="ms-auto small text-decoration-none text-muted" data-bs-toggle="collapse"
                           href="#help-active" role="button" aria-expanded="false" aria-controls="help-active">
                            <i class="bi bi-question-circle"></i> Apa ini?
                        </a>
                    </div>
                    <div class="collapse" id="help-active">
                        <div class="border rounded bg-light-subtle p-2 mt-2 small text-muted">
                            Kalau dimatikan, webhook untuk domain ini tidak diteruskan dan tercatat
                            sebagai <em>Tidak ditemukan</em> di Logs.
                        </div>
                    </div>

                    <div class="form-text text-warning mt-2 d-none" id="has-logs-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Domain ini sudah punya log. Mengubah URL akan mengupdate domain identifier secara otomatis.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark" id="btn-save">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Toast notif copy --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="copyToast" class="toast align-items-center text-bg-dark border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>Relay URL berhasil disalin
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const modalEl = document.getElementById('domainModal');
    const modal   = new bootstrap.Modal(modalEl);

    const table = $('#tbl-domains').DataTable({
        processing:  true,
        serverSide:  true,   // paging & filter dikerjakan MySQL, bukan browser
        deferRender: true,
        stateSave:   true,
        pageLength:  10,
        lengthMenu:  [10, 25, 50, 100],
        order:       [[0, 'asc']],
        language:    window.DT_LANG,
        ajax: {
            url: '{{ route('panel.domains.data') }}',
            data: function (d) {
                d.provider  = $('#f-provider').val();
                d.is_active = $('#f-active').val();
            },
        },
        columns: [
            { data: 'name', className: 'fw-medium', render: (v) => escapeHtml(v) },
            {
                data: 'alias', className: 'text-nowrap d-none d-md-table-cell',
                render: (v) => '<code class="bg-light border rounded px-2 py-1">-' + escapeHtml(v) + '</code>',
            },
            { data: 'provider', render: (v) => providerBadge(v) },
            { data: 'notes', className: 'text-muted small d-none d-lg-table-cell', render: (v) => escapeHtml(v) },
            {
                data: 'target_url', className: 'text-muted small d-none d-md-table-cell',
                render: (v) => '<span class="d-inline-block text-truncate" style="max-width:280px" title="' +
                    escapeHtml(v) + '">' + escapeHtml(v) + '</span>',
            },
            {
                data: 'is_active', orderable: true,
                render: (v) => v
                    ? '<span class="badge bg-success-subtle text-success">Aktif</span>'
                    : '<span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>',
            },
            {
                data: null, orderable: false, searchable: false, className: 'text-end text-nowrap',
                render: function (row) {
                    // Di layar kecil tombol tampil sebagai ikon saja agar muat.
                    const label = (icon, text) =>
                        '<i class="bi ' + icon + ' d-md-none"></i>' +
                        '<span class="d-none d-md-inline">' + text + '</span>';

                    return '' +
                        '<a href="' + row.logs_url + '" class="btn btn-outline-secondary" title="Lihat log">' +
                            '<i class="bi bi-journal-text"></i></a> ' +
                        '<button class="btn btn-outline-secondary btn-edit" data-id="' + row.id + '" title="Edit">' +
                            label('bi-pencil', 'Edit') + '</button> ' +
                        '<a href="' + row.test_url + '" class="btn btn-outline-secondary" title="Test">' +
                            label('bi-send', 'Test') + '</a> ' +
                        '<button class="btn btn-outline-danger btn-delete" data-id="' + row.id + '" ' +
                            'data-name="' + escapeHtml(row.name) + '" title="Hapus">' +
                            label('bi-trash', 'Hapus') + '</button>';
                },
            },
        ],
    });

    $('#f-provider, #f-active').on('change', () => table.ajax.reload());
    $('#btn-reset').on('click', function () {
        $('#f-provider, #f-active').val('');
        table.search('').ajax.reload();
    });

    // ---- Form helpers ----
    function clearErrors() {
        $('#domain-form .is-invalid').removeClass('is-invalid');
        $('#domain-form [data-error]').text('');
    }

    // Keterangan "Apa ini?" selalu kembali tertutup tiap modal dibuka
    function collapseHelp() {
        $('#domainModal .collapse').removeClass('show');
        $('#domainModal [data-bs-toggle="collapse"]').attr('aria-expanded', 'false');
    }

    function showErrors(errors) {
        clearErrors();
        Object.keys(errors || {}).forEach(function (field) {
            const box = $('#domain-form [data-error="' + field + '"]');
            box.text(errors[field][0]);
            box.siblings('.form-control, .form-select').addClass('is-invalid');
            box.parent().find('.form-control, .form-select').addClass('is-invalid');
        });
    }

    function previewDomain(url) {
        const preview = $('#domain-preview');
        try {
            const parsed = new URL(url);
            preview.html('Domain terdeteksi: <strong>' + escapeHtml(parsed.hostname) + '</strong>')
                   .attr('class', 'form-text text-success');
        } catch {
            preview.html('').attr('class', 'form-text');
        }
    }

    $('#fm-target_url').on('input', function () { previewDomain(this.value); });

    // ---- Create ----
    $('#btn-create').on('click', function () {
        clearErrors();
        collapseHelp();
        $('#domain-form')[0].reset();
        $('#f-id').val('');
        $('#fm-is_active').prop('checked', true);
        $('#domain-preview').html('');
        $('#has-logs-warning').addClass('d-none');
        $('#alias-box').addClass('d-none');   // alias baru ada setelah domain tersimpan
        $('#domainModalTitle').text('Tambah Domain');
        modal.show();
    });

    // ---- Edit ----
    $('#tbl-domains').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearErrors();
        collapseHelp();
        $.getJSON('{{ url('panel/domains') }}/' + id, function (d) {
            $('#f-id').val(d.id);
            $('#fm-provider').val(d.provider);
            $('#fm-target_url').val(d.target_url);
            $('#fm-notes').val(d.notes || '');
            $('#fm-is_active').prop('checked', d.is_active);
            $('#has-logs-warning').toggleClass('d-none', !d.has_logs);
            $('#fm-alias').val(d.alias || '');
            $('#alias-sample').text(d.alias || 'XXX');
            $('#alias-box').toggleClass('d-none', !d.alias);
            previewDomain(d.target_url);
            $('#domainModalTitle').text('Edit Domain');
            modal.show();
        }).fail(() => notify('Gagal memuat data domain.', 'error'));
    });

    // ---- Simpan (create / update) ----
    $('#domain-form').on('submit', function (e) {
        e.preventDefault();
        const id  = $('#f-id').val();
        const url = id ? '{{ url('panel/domains') }}/' + id : '{{ route('panel.domains.store') }}';

        const payload = {
            _method:    id ? 'PUT' : 'POST',
            provider:   $('#fm-provider').val(),
            target_url: $('#fm-target_url').val(),
            notes:      $('#fm-notes').val(),
            is_active:  $('#fm-is_active').is(':checked') ? 1 : 0,
        };

        $('#btn-save').prop('disabled', true);

        $.post(url, payload)
            .done(function (res) {
                modal.hide();
                table.ajax.reload(null, false);
                notify(res.message);
            })
            .fail(function (xhr) {
                if (xhr.status === 422) showErrors(xhr.responseJSON.errors);
                else notify('Gagal menyimpan domain.', 'error');
            })
            .always(() => $('#btn-save').prop('disabled', false));
    });

    // ---- Hapus ----
    $('#tbl-domains').on('click', '.btn-delete', async function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');

        const ok = await confirmAction({
            title: 'Hapus domain?',
            text:  'Domain ' + name + ' akan dihapus permanen.',
            btn:   'Ya, hapus',
        });
        if (!ok) return;

        $.post('{{ url('panel/domains') }}/' + id, { _method: 'DELETE' })
            .done(function (res) {
                table.ajax.reload(null, false);
                notify(res.message);
            })
            .fail(() => notify('Gagal menghapus domain.', 'error'));
    });

    // ---- Salin alias ----
    $('#btn-copy-alias').on('click', function () {
        const alias = $('#fm-alias').val();
        if (!alias) return;

        navigator.clipboard?.writeText(alias).then(() => notify('Alias ' + alias + ' disalin.'));
    });

    // ---- Copy relay URL ----
    const toast = new bootstrap.Toast(document.getElementById('copyToast'), { delay: 2000 });

    $('#copy-relay-btn').on('click', function () {
        const url = $('#relay-url').text().trim();
        const btn = this;

        const done = () => {
            const icon = $(btn).find('i');
            icon.removeClass('bi-copy').addClass('bi-check-lg');
            toast.show();
            setTimeout(() => icon.removeClass('bi-check-lg').addClass('bi-copy'), 2000);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(done);
        } else {
            const el = document.createElement('textarea');
            el.value = url;
            el.style.position = 'absolute';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            done();
        }
    });
});
</script>
@endpush
