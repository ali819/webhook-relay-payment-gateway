@extends('layouts.app')
@section('title', 'Logs')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h5 class="fw-semibold mb-0">Webhook Logs</h5>
        <p class="text-muted small mb-0">Riwayat semua webhook masuk</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="form-check form-switch d-flex align-items-center gap-2 me-2 mb-0">
            <input class="form-check-input mt-0" type="checkbox" id="auto-refresh">
            <label class="form-check-label small text-muted" for="auto-refresh">Auto refresh 10s</label>
        </div>
        <button class="btn btn-outline-secondary" id="btn-refresh">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#pruneModal">
            <i class="bi bi-trash me-1"></i>Bersihkan log
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Domain</label>
                <select id="f-domain" class="form-select">
                    <option value="">Semua domain</option>
                    @foreach($domains as $d)
                        <option value="{{ $d->id }}" {{ request('domain_id') == $d->id ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Provider</label>
                <select id="f-provider" class="form-select">
                    <option value="">Semua</option>
                    @foreach(\App\Models\Domain::PROVIDERS as $p)
                        <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                    @endforeach
                    <option value="unknown">Unknown</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select id="f-status" class="form-select">
                    <option value="">Semua</option>
                    <option value="success">Sukses</option>
                    <option value="failed">Gagal</option>
                    <option value="invalid_signature">Signature invalid</option>
                    <option value="domain_not_found">Domain tidak ditemukan</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Dari tanggal</label>
                <input type="date" id="f-from" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Sampai</label>
                <input type="date" id="f-to" class="form-control">
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
            <table class="table table-hover align-middle small w-100" id="tbl-logs">
                <thead class="table-light">
                    <tr>
                        <th>Waktu</th>
                        <th class="d-none d-md-table-cell">Domain</th>
                        <th>Provider</th>
                        <th class="d-none d-lg-table-cell">Event</th>
                        <th class="d-none d-xl-table-cell">Domain key</th>
                        <th>Status</th>
                        <th class="d-none d-lg-table-cell">HTTP</th>
                        <th class="d-none d-lg-table-cell">Durasi</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Modal detail log --}}
<div class="modal fade" id="logModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-semibold">Detail Log</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="log-detail-body">
                <div class="text-center text-muted py-4">Memuat...</div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <a href="#" class="btn btn-outline-secondary" id="log-open-page" target="_blank">Buka halaman detail</a>
                <button class="btn btn-dark" id="btn-retry">
                    <i class="bi bi-arrow-repeat me-1"></i>Kirim ulang
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal prune --}}
<div class="modal fade" id="pruneModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-semibold">Bersihkan log</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="prune-form">
                <div class="modal-body">
                    <p class="text-muted small mb-3">Pilih berapa log terbaru yang ingin <strong>dipertahankan</strong>. Log sisanya akan dihapus permanen.</p>
                    <div class="d-flex flex-column gap-2">
                        @foreach([1000 => '1.000', 100 => '100', 50 => '50'] as $val => $label)
                        <label class="border rounded p-3 d-flex align-items-center gap-3" style="cursor:pointer">
                            <input type="radio" name="keep" value="{{ $val }}" {{ $loop->first ? 'checked' : '' }} class="form-check-input mt-0">
                            <div>
                                <div class="fw-medium">Sisakan {{ $label }} log terbaru</div>
                                <div class="text-muted small">Hapus semua log di luar {{ $label }} terbaru</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Hapus sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const STATUS_META = {
        success:           ['bg-success-subtle text-success',     'Sukses'],
        failed:            ['bg-danger-subtle text-danger',       'Gagal'],
        invalid_signature: ['bg-warning-subtle text-warning',     'Signature invalid'],
        domain_not_found:  ['bg-secondary-subtle text-secondary', 'Tidak ditemukan'],
    };

    function statusBadge(s) {
        const meta = STATUS_META[s] || ['bg-light text-muted', s || '-'];
        return '<span class="badge ' + meta[0] + '">' + escapeHtml(meta[1]) + '</span>';
    }

    const logModal = new bootstrap.Modal(document.getElementById('logModal'));
    let currentLog = null;

    const table = $('#tbl-logs').DataTable({
        processing:  true,
        serverSide:  true,   // wajib: log bisa jutaan baris
        deferRender: true,
        stateSave:   true,
        pageLength:  20,
        lengthMenu:  [20, 50, 100, 200],
        order:       [[0, 'desc']],
        language:    window.DT_LANG,
        ajax: {
            url: '{{ route('panel.logs.data') }}',
            data: function (d) {
                d.domain_id = $('#f-domain').val();
                d.provider  = $('#f-provider').val();
                d.status    = $('#f-status').val();
                d.date_from = $('#f-from').val();
                d.date_to   = $('#f-to').val();
            },
        },
        columns: [
            { data: 'created_at', className: 'text-muted', render: (v) => escapeHtml(v) },
            { data: 'domain', orderable: false, className: 'd-none d-md-table-cell', render: (v) => escapeHtml(v) },
            { data: 'provider', render: (v) => providerBadge(v) },
            { data: 'event_type', className: 'text-muted d-none d-lg-table-cell', render: (v) => escapeHtml(v) },
            { data: 'custom_field1', className: 'd-none d-xl-table-cell', render: (v) => '<code class="text-muted">' + escapeHtml(v) + '</code>' },
            { data: 'status', render: (v) => statusBadge(v) },
            { data: 'response_code', className: 'text-muted d-none d-lg-table-cell', render: (v) => escapeHtml(v) },
            { data: 'duration_ms', className: 'text-muted d-none d-lg-table-cell', render: (v) => v ? v + 'ms' : '-' },
            {
                data: null, orderable: false, searchable: false, className: 'text-end',
                render: (row) => '<button class="btn btn-outline-secondary btn-detail" data-id="' + row.id +
                                 '" data-url="' + row.show_url + '" title="Detail">' +
                                 '<i class="bi bi-eye d-md-none"></i>' +
                                 '<span class="d-none d-md-inline">Detail</span></button>',
            },
        ],
    });

    // ---- Filter ----
    $('#f-domain, #f-provider, #f-status, #f-from, #f-to').on('change', () => table.ajax.reload());
    $('#btn-reset').on('click', function () {
        $('#f-domain, #f-provider, #f-status, #f-from, #f-to').val('');
        table.search('').ajax.reload();
    });
    $('#btn-refresh').on('click', () => table.ajax.reload(null, false));

    // Filter awal dari querystring (?domain_id= dari halaman Domains)
    @if(request('domain_id'))
        $('#f-domain').val('{{ (int) request('domain_id') }}');
        table.ajax.reload();
    @endif

    // ---- Auto refresh: default mati, pilihan disimpan di localStorage ----
    const AUTO_KEY = 'wrpg.logs.auto-refresh';
    let timer = null;

    function startAuto() {
        stopAuto();
        timer = setInterval(() => {
            if (!document.hidden) table.ajax.reload(null, false);
        }, 10000);
    }
    function stopAuto() { if (timer) { clearInterval(timer); timer = null; } }

    // localStorage bisa dilempar error (private mode / site data diblokir),
    // jadi kegagalan baca-tulis diabaikan dan default-nya tetap mati.
    function readPref() {
        try { return localStorage.getItem(AUTO_KEY) === '1'; } catch { return false; }
    }
    function writePref(on) {
        try { localStorage.setItem(AUTO_KEY, on ? '1' : '0'); } catch {}
    }

    $('#auto-refresh')
        .prop('checked', readPref())
        .on('change', function () {
            writePref(this.checked);
            this.checked ? startAuto() : stopAuto();
        });

    if ($('#auto-refresh').is(':checked')) startAuto();

    // ---- Detail ----
    $('#tbl-logs').on('click', '.btn-detail', function () {
        const url = $(this).data('url');
        $('#log-open-page').attr('href', url);
        $('#log-detail-body').html('<div class="text-center text-muted py-4">Memuat...</div>');
        logModal.show();

        $.getJSON(url, function (d) {
            currentLog = d;
            $('#btn-retry').prop('disabled', !d.can_retry);

            const row = (label, value) =>
                '<div class="col-6 col-md-4 mb-3"><div class="text-muted small">' + label +
                '</div><div>' + value + '</div></div>';

            let html = '<div class="row">' +
                row('Waktu', escapeHtml(d.created_at)) +
                row('Domain', escapeHtml(d.domain)) +
                row('Provider', providerBadge(d.provider)) +
                row('Event', escapeHtml(d.event_type)) +
                row('Domain key', '<code>' + escapeHtml(d.custom_field1) + '</code>') +
                row('Status', statusBadge(d.status)) +
                row('HTTP', escapeHtml(d.response_code)) +
                row('Durasi', d.duration_ms ? d.duration_ms + 'ms' : '-') +
                '</div>';

            if (d.error_message) {
                html += '<div class="alert alert-danger small"><strong>Error:</strong><br>' +
                        escapeHtml(d.error_message) + '</div>';
            }

            html += '<div class="text-muted small mb-1">Payload</div>' +
                    '<pre class="bg-light p-3 rounded small mb-0" style="max-height:320px;overflow:auto">' +
                    escapeHtml(JSON.stringify(d.payload, null, 2)) + '</pre>';

            $('#log-detail-body').html(html);
        }).fail(() => $('#log-detail-body').html('<div class="text-danger py-4">Gagal memuat detail log.</div>'));
    });

    // ---- Retry ----
    $('#btn-retry').on('click', async function () {
        if (!currentLog) return;

        const ok = await confirmAction({
            title: 'Kirim ulang webhook?',
            text:  'Payload akan dikirim ulang ke target URL.',
            btn:   'Ya, kirim',
            icon:  'question',
        });
        if (!ok) return;

        const btn = $(this).prop('disabled', true);

        $.post(currentLog.retry_url)
            .done(function (res) {
                logModal.hide();
                table.ajax.reload(null, false);
                notify(res.message, res.status === 'success' ? 'success' : 'warning');
            })
            .fail((xhr) => notify(xhr.responseJSON?.message || 'Gagal kirim ulang.', 'error'))
            .always(() => btn.prop('disabled', false));
    });

    // ---- Prune ----
    $('#prune-form').on('submit', async function (e) {
        e.preventDefault();

        const ok = await confirmAction({
            title: 'Bersihkan log?',
            text:  'Log lama akan dihapus permanen dan tidak bisa dikembalikan.',
            btn:   'Ya, hapus',
        });
        if (!ok) return;

        $.post('{{ route('panel.logs.prune') }}', {
            _method: 'DELETE',
            keep:    $('#prune-form input[name=keep]:checked').val(),
        })
            .done(function (res) {
                bootstrap.Modal.getInstance(document.getElementById('pruneModal')).hide();
                table.ajax.reload(null, false);
                notify(res.message);
            })
            .fail(() => notify('Gagal membersihkan log.', 'error'));
    });
});
</script>
@endpush
