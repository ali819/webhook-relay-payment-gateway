@extends('layouts.app')
@section('title', 'Tutorial')
@section('content')
<div class="mb-4">
    <h5 class="fw-semibold mb-0">Tutorial & Aturan</h5>
    <p class="text-muted small mb-0">Panduan penggunaan Webhook Relay</p>
</div>

<div class="row g-4">

    {{-- Intro --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-broadcast me-2 text-muted"></i>Apa itu Webhook Relay Payment Gateway?
                </h6>
                <hr>
                <p class="mb-3">
                    Webhook Relay PG adalah layanan perantara yang meneruskan notifikasi pembayaran dari payment gateway
                    (Midtrans & Xendit) ke aplikasi-aplikasi kamu — cukup dengan <strong>satu akun payment gateway</strong>
                    untuk banyak aplikasi.
                </p>
                <p class="mb-3 text-muted small">
                    Normalnya, setiap aplikasi butuh akun Midtrans/Xendit sendiri-sendiri hanya untuk bisa menerima
                    webhook pembayaran. Dengan relay ini, satu akun payment gateway bisa dipakai bersama — relay yang
                    bertugas memverifikasi dan meneruskan webhook ke masing-masing aplikasi yang tepat secara otomatis.
                </p>
                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-4">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="mt-1"><i class="bi bi-diagram-2 text-muted fs-5"></i></div>
                            <div>
                                <div class="fw-medium small">Satu akun, banyak apps</div>
                                <div class="text-muted small">Tidak perlu daftar akun payment gateway baru untuk setiap aplikasi.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="mt-1"><i class="bi bi-shield-check text-muted fs-5"></i></div>
                            <div>
                                <div class="fw-medium small">Verifikasi signature otomatis</div>
                                <div class="text-muted small">Setiap webhook diverifikasi keasliannya sebelum diteruskan.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="mt-1"><i class="bi bi-journal-text text-muted fs-5"></i></div>
                            <div>
                                <div class="fw-medium small">Log terpusat</div>
                                <div class="text-muted small">Semua aktivitas webhook dari semua aplikasi bisa dipantau di satu tempat.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cara kerja --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
                <h6 class="fw-semibold mb-3"><i class="bi bi-diagram-3 me-2 text-muted"></i>Cara kerja relay</h6>
                <hr>
                <ol class="mb-0" style="line-height:2">
                    <li>Daftarkan domain di panel ini — isi nama, provider, domain identifier, target URL, dan secret key.</li>
                    <li>Gunakan URL relay berikut sebagai webhook URL di dashboard Midtrans/Xendit:
                        <div class="mt-2 mb-1 d-flex flex-wrap align-items-center gap-2">
                            <code class="bg-light px-3 py-2 rounded text-break" id="relay-url">{{ route('handleApi') }}</code>
                            <button class="btn btn-sm btn-outline-secondary flex-shrink-0" onclick="copyRelayUrl(this)" title="Copy URL">
                                <i class="bi bi-copy" style="font-size:12px"></i>
                            </button>
                        </div>
                    </li>
                    <li>Di setiap pembuatan transaksi di app kamu, sertakan identifier domain sesuai provider.</li>
                    <li>Relay akan memverifikasi signature lalu meneruskan payload ke target URL yang terdaftar.</li>
                    <li>Semua aktivitas tercatat di halaman <a href="{{ route('panel.logs.index') }}">Logs</a>.</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Midtrans --}}
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <h6 class="fw-semibold mb-3">
                    <span class="badge badge-midtrans me-2">Midtrans</span>Cara integrasi
                </h6>
                <hr>
                <p class="small text-muted mb-2">Saat membuat transaksi Snap, tambahkan <code>custom_field1</code> berisi domain identifier yang sudah didaftarkan:</p>
                <pre class="bg-light rounded p-3 small mb-3" style="overflow-x:auto"><code>$params = [
    'transaction_details' => [
        'order_id'     => 'INV-001',
        'gross_amount' => 100000,
    ],
    'custom_field1' => 'example.com', // ← domain identifier
];</code></pre>
                <p class="small text-muted mb-2">Secret key yang diisi di panel adalah <strong>Server Key</strong> Midtrans kamu. Bisa ditemukan di:</p>
                <p class="small mb-0"><i class="bi bi-arrow-right me-1"></i>Dashboard Midtrans → Settings → Access Keys → <strong>Server Key</strong></p>
            </div>
        </div>
    </div>

    {{-- Xendit --}}
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <h6 class="fw-semibold mb-3">
                    <span class="badge badge-xendit me-2">Xendit</span>Cara integrasi
                </h6>
                <hr>
                <p class="small text-muted mb-2">Saat membuat transaksi, tambahkan <code>metadata.domain</code> berisi domain identifier yang sudah didaftarkan:</p>
                <pre class="bg-light rounded p-3 small mb-3" style="overflow-x:auto"><code>$params = [
    'external_id' => 'INV-001',
    'amount'      => 100000,
    'metadata'    => [
        'domain' => 'example.com', // ← domain identifier
    ],
];</code></pre>
                <p class="small text-muted mb-2">Secret key yang diisi di panel adalah <strong>Webhook Token</strong> Xendit kamu. Bisa ditemukan di:</p>
                <p class="small mb-0"><i class="bi bi-arrow-right me-1"></i>Dashboard Xendit → Settings → Developers → <strong>Webhook Token</strong></p>
            </div>
        </div>
    </div>

    {{-- Aturan --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
                <h6 class="fw-semibold mb-3"><i class="bi bi-shield-check me-2 text-muted"></i>Aturan & ketentuan</h6>
                <hr>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <ul class="small mb-0" style="line-height:2">
                            <li>Domain identifier harus <strong>unik</strong> dan konsisten di semua transaksi.</li>
                            <li>Jangan ubah domain identifier jika sudah ada transaksi berjalan.</li>
                            <li>Target URL harus <strong>dapat diakses publik</strong> (bukan localhost).</li>
                            <li>Target URL harus merespons dengan HTTP <strong>2xx</strong> agar log tercatat sukses.</li>
                        </ul>
                    </div>
                    <div class="col-12 col-md-6">
                        <ul class="small mb-0" style="line-height:2">
                            <li>Secret key disimpan terenkripsi — jangan bagikan ke siapapun.</li>
                            <li>Webhook yang gagal diverifikasi signature-nya akan ditolak dan dicatat sebagai <code>invalid_signature</code>.</li>
                            <li>Domain yang dinonaktifkan tidak akan menerima webhook — status akan <code>domain_not_found</code>.</li>
                            <li>Relay tidak menyimpan data kartu atau informasi sensitif pembayaran.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status log --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
                <h6 class="fw-semibold mb-3"><i class="bi bi-journal-text me-2 text-muted"></i>Arti status log</h6>
                <hr>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th>Artinya</th>
                                <th class="d-none d-md-table-cell">Solusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-success-subtle text-success">Sukses</span></td>
                                <td>Webhook berhasil diteruskan ke target URL</td>
                                <td class="d-none d-md-table-cell">—</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger-subtle text-danger">Gagal</span></td>
                                <td>Target URL tidak merespons atau error</td>
                                <td class="d-none d-md-table-cell">Cek apakah target URL aktif dan merespons 2xx</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning-subtle text-warning">Signature invalid</span></td>
                                <td>Secret key tidak cocok atau payload dimanipulasi</td>
                                <td class="d-none d-md-table-cell">Pastikan secret key di panel sesuai dengan yang di dashboard provider</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-secondary-subtle text-secondary">Domain tidak ditemukan</span></td>
                                <td>Nilai <code>custom_field1</code> / <code>metadata.domain</code> tidak terdaftar atau domain nonaktif</td>
                                <td class="d-none d-md-table-cell">Pastikan domain identifier terdaftar di panel dan statusnya aktif</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function copyRelayUrl(btn) {
    const url = document.getElementById('relay-url').innerText.trim();

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(() => showCopied(btn));
    } else {
        const el = document.createElement('textarea');
        el.value = url;
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        showCopied(btn);
    }
}

function showCopied(btn) {
    const icon = btn.querySelector('i');
    icon.classList.replace('bi-copy', 'bi-check-lg');
    setTimeout(() => icon.classList.replace('bi-check-lg', 'bi-copy'), 2000);
}
</script>
@endsection
