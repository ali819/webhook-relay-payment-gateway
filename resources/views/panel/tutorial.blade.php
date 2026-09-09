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
                    (Midtrans, Xendit & DOKU) ke aplikasi-aplikasi kamu — cukup dengan <strong>satu akun payment gateway</strong>
                    untuk banyak aplikasi.
                </p>
                <p class="mb-3 text-muted small">
                    Normalnya, setiap aplikasi butuh akun Midtrans/Xendit/DOKU sendiri-sendiri hanya untuk bisa menerima
                    webhook pembayaran. Dengan relay ini, satu akun payment gateway bisa dipakai bersama — relay yang
                    bertugas mengenali tujuan dan meneruskan webhook ke masing-masing aplikasi yang tepat secara otomatis.
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
                                <div class="fw-medium small">Header signature diteruskan</div>
                                <div class="text-muted small">Header asli tiap provider ikut dikirim, jadi app tujuan tetap bisa memverifikasi sendiri.</div>
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

    {{-- Diagram alur & mapping --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
                <h6 class="fw-semibold mb-3"><i class="bi bi-signpost-split me-2 text-muted"></i>Alur &amp; pemetaan domain</h6>
                <hr>
                <p class="small text-muted mb-3">
                    Satu URL relay menerima webhook dari semua payment gateway, lalu diteruskan ke aplikasi
                    yang tepat berdasarkan <strong>domain identifier</strong> yang kamu sisipkan di transaksi.
                </p>

                <div class="small text-muted d-lg-none mb-2">
                    <i class="bi bi-arrow-left-right me-1"></i>Geser diagram ke samping untuk melihat semuanya.
                </div>

                <div class="overflow-x-auto pb-2">
                    <svg viewBox="0 0 980 470" role="img" width="980"
                         style="min-width:900px;max-width:100%;height:auto"
                         aria-label="Diagram: payment gateway mengirim webhook ke satu URL relay, relay membaca domain identifier lalu meneruskan ke aplikasi yang cocok">
                        <title>Alur webhook dan pemetaan domain identifier</title>

                        <defs>
                            <marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5"
                                    markerWidth="7" markerHeight="7" orient="auto-start-reverse">
                                <path d="M0,0 L10,5 L0,10 z" fill="#adb5bd"/>
                            </marker>
                        </defs>

                        <!-- judul kolom -->
                        <text x="20"  y="26" font-size="13" font-weight="600" fill="#212529">1 &middot; Payment gateway</text>
                        <text x="360" y="26" font-size="13" font-weight="600" fill="#212529">2 &middot; Webhook Relay</text>
                        <text x="700" y="26" font-size="13" font-weight="600" fill="#212529">3 &middot; Aplikasi kamu</text>

                        <!-- ===== kolom 1: provider ===== -->
                        <g font-size="12">
                            <rect x="20" y="44" width="270" height="96" rx="10" fill="#fff" stroke="#dee2e6"/>
                            <rect x="36" y="60" width="72" height="20" rx="10" fill="#00b4d8"/>
                            <text x="72" y="74" text-anchor="middle" fill="#fff" font-size="11" font-weight="600">Midtrans</text>
                            <text x="36" y="102" fill="#6c757d">kirim di transaksi:</text>
                            <text x="36" y="122" font-family="ui-monospace,monospace" fill="#d63384">custom_field1</text>

                            <rect x="20" y="156" width="270" height="96" rx="10" fill="#fff" stroke="#dee2e6"/>
                            <rect x="36" y="172" width="62" height="20" rx="10" fill="#4f46e5"/>
                            <text x="67" y="186" text-anchor="middle" fill="#fff" font-size="11" font-weight="600">Xendit</text>
                            <text x="36" y="214" fill="#6c757d">kirim di transaksi:</text>
                            <text x="36" y="234" font-family="ui-monospace,monospace" fill="#d63384">metadata.domain</text>

                            <rect x="20" y="268" width="270" height="96" rx="10" fill="#fff" stroke="#dee2e6"/>
                            <rect x="36" y="284" width="52" height="20" rx="10" fill="#f97316"/>
                            <text x="62" y="298" text-anchor="middle" fill="#fff" font-size="11" font-weight="600">DOKU</text>
                            <text x="36" y="326" fill="#6c757d">kirim di transaksi:</text>
                            <text x="36" y="346" font-family="ui-monospace,monospace" fill="#d63384">additional_info.domain</text>
                        </g>

                        <!-- panah provider -> relay -->
                        <g stroke="#adb5bd" stroke-width="1.5" fill="none" marker-end="url(#arrow)">
                            <path d="M290 92  C 320 92, 320 150, 350 150"/>
                            <path d="M290 204 C 320 204, 320 204, 350 204"/>
                            <path d="M290 316 C 320 316, 320 258, 350 258"/>
                        </g>

                        <!-- ===== kolom 2: relay ===== -->
                        <rect x="350" y="44" width="290" height="320" rx="12" fill="#f8f9fa" stroke="#212529" stroke-width="1.5"/>
                        <text x="370" y="72" font-size="12" font-family="ui-monospace,monospace" fill="#6c757d">POST /api/webhook/&hellip;</text>
                        <line x1="366" y1="86" x2="624" y2="86" stroke="#dee2e6"/>

                        <g font-size="12" fill="#212529">
                            <text x="370" y="112" font-weight="600">a. Deteksi provider</text>
                            <text x="370" y="130" font-size="11" fill="#6c757d">dari header &amp; bentuk payload</text>

                            <text x="370" y="164" font-weight="600">b. Baca domain identifier</text>
                            <text x="370" y="182" font-size="11" fill="#6c757d">dicari rekursif di payload</text>

                            <text x="370" y="216" font-weight="600">c. Cocokkan ke tabel Domains</text>
                            <text x="370" y="234" font-size="11" fill="#6c757d">domain + provider, harus aktif</text>

                            <text x="370" y="268" font-weight="600">d. Teruskan ke Target URL</text>
                            <text x="370" y="286" font-size="11" fill="#6c757d">header signature ikut dikirim</text>

                            <text x="370" y="320" font-weight="600">e. Catat ke Logs</text>
                            <text x="370" y="338" font-size="11" fill="#6c757d">payload, status, durasi</text>
                        </g>

                        <!-- panah relay -> aplikasi -->
                        <g stroke="#adb5bd" stroke-width="1.5" fill="none" marker-end="url(#arrow)">
                            <path d="M640 150 C 665 150, 665 92,  690 92"/>
                            <path d="M640 204 C 665 204, 665 204, 690 204"/>
                            <path d="M640 258 C 665 258, 665 316, 690 316"/>
                        </g>

                        <!-- ===== kolom 3: aplikasi ===== -->
                        <g font-size="12">
                            <rect x="690" y="44" width="270" height="96" rx="10" fill="#fff" stroke="#dee2e6"/>
                            <text x="706" y="70" font-weight="600" fill="#212529">toko-a.com</text>
                            <text x="706" y="92" font-size="11" fill="#6c757d">Target URL:</text>
                            <text x="706" y="112" font-size="11" font-family="ui-monospace,monospace" fill="#d63384">https://toko-a.com/api/callback</text>
                            <rect x="706" y="120" width="58" height="16" rx="8" fill="#d1e7dd"/>
                            <text x="735" y="132" font-size="10" fill="#0f5132" text-anchor="middle">Midtrans</text>

                            <rect x="690" y="156" width="270" height="96" rx="10" fill="#fff" stroke="#dee2e6"/>
                            <text x="706" y="182" font-weight="600" fill="#212529">toko-b.com</text>
                            <text x="706" y="204" font-size="11" fill="#6c757d">Target URL:</text>
                            <text x="706" y="224" font-size="11" font-family="ui-monospace,monospace" fill="#d63384">https://toko-b.com/webhook/xendit</text>
                            <rect x="706" y="232" width="46" height="16" rx="8" fill="#d1e7dd"/>
                            <text x="729" y="244" font-size="10" fill="#0f5132" text-anchor="middle">Xendit</text>

                            <rect x="690" y="268" width="270" height="96" rx="10" fill="#fff" stroke="#dee2e6"/>
                            <text x="706" y="294" font-weight="600" fill="#212529">shop-c.id</text>
                            <text x="706" y="316" font-size="11" fill="#6c757d">Target URL:</text>
                            <text x="706" y="336" font-size="11" font-family="ui-monospace,monospace" fill="#d63384">https://shop-c.id/pay/doku</text>
                            <rect x="706" y="344" width="40" height="16" rx="8" fill="#d1e7dd"/>
                            <text x="726" y="356" font-size="10" fill="#0f5132" text-anchor="middle">DOKU</text>
                        </g>

                        <!-- ===== kunci pemetaan ===== -->
                        <rect x="20" y="392" width="940" height="62" rx="10" fill="#fff" stroke="#dee2e6" stroke-dasharray="4 3"/>
                        <text x="40" y="416" font-size="12" font-weight="600" fill="#212529">Kunci pemetaan</text>
                        <text x="40" y="438" font-size="12" fill="#6c757d">
                            nilai yang kamu kirim di transaksi
                        </text>
                        <text x="272" y="438" font-size="12" font-family="ui-monospace,monospace" fill="#d63384">toko-a.com</text>
                        <text x="368" y="438" font-size="12" fill="#6c757d">harus sama persis dengan</text>
                        <text x="540" y="438" font-size="12" fill="#6c757d">host dari Target URL &rarr;</text>
                        <text x="700" y="438" font-size="12" font-family="ui-monospace,monospace" fill="#d63384">https://toko-a.com/api/callback</text>
                    </svg>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-medium small mb-1"><i class="bi bi-check-circle text-success me-1"></i>Kalau cocok</div>
                            <div class="small text-muted">
                                Payload diteruskan ke Target URL. Status log <span class="badge bg-success-subtle text-success">Sukses</span>
                                bila aplikasi tujuan membalas HTTP 2xx, atau <span class="badge bg-danger-subtle text-danger">Gagal</span>
                                bila tidak merespons / membalas error — bisa dikirim ulang dari halaman Logs.
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-medium small mb-1"><i class="bi bi-x-circle text-secondary me-1"></i>Kalau tidak cocok</div>
                            <div class="small text-muted">
                                Identifier salah ketik, belum didaftarkan, atau domainnya nonaktif &rarr; status
                                <span class="badge bg-secondary-subtle text-secondary">Tidak ditemukan</span>.
                                Payload tetap tersimpan di Logs, jadi bisa dicek nilai apa yang sebenarnya terkirim.
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
                    <li>
                        Daftarkan aplikasi tujuan di menu <a href="{{ route('panel.domains.index') }}">Domains</a> &rarr;
                        <strong>Tambah Domain</strong>.
                        <div class="small text-muted mt-2 mb-3 ps-1" style="line-height:1.8">
                            <div class="mb-2">
                                <strong class="text-body">Provider</strong> — payment gateway yang akan mengirim
                                webhook untuk aplikasi itu. Kalau satu aplikasi menerima dari dua PG (mis. Midtrans
                                dan DOKU), buat <em>dua entri</em> dengan target URL masing-masing.
                            </div>
                            <div class="mb-2">
                                <strong class="text-body">Target URL</strong> — URL lengkap endpoint di aplikasi kamu
                                yang menerima notifikasi pembayaran, mis.
                                <code>https://toko-a.com/api/midtrans/callback</code>. Inilah endpoint yang
                                <em>biasanya</em> kamu daftarkan langsung ke dashboard PG; sekarang cukup didaftarkan
                                di sini. Syaratnya: bisa diakses publik (bukan localhost) dan membalas HTTP 2xx.
                            </div>
                            <div class="mb-2">
                                <strong class="text-body">Domain identifier</strong> — diambil <em>otomatis</em> dari
                                host target URL (dari contoh di atas: <code>toko-a.com</code>). Nilai inilah yang harus
                                kamu kirim di setiap transaksi supaya relay tahu webhook-nya milik aplikasi mana.
                            </div>
                            <div class="mb-2">
                                <strong class="text-body">Keterangan</strong> — label bebas (Production, Sandbox, dll)
                                supaya mudah dibedakan di daftar. Tidak berpengaruh ke jalannya relay.
                            </div>
                            <div>
                                <strong class="text-body">Aktif</strong> — kalau dimatikan, webhook untuk domain itu
                                ditolak dan tercatat sebagai <em>Tidak ditemukan</em> di Logs.
                            </div>
                        </div>
                    </li>
                    <li>Gunakan URL relay berikut sebagai webhook URL di dashboard Midtrans/Xendit/DOKU:
                        <div class="mt-2 mb-1 d-flex flex-wrap align-items-center gap-2">
                            <code class="bg-light px-3 py-2 rounded text-break" id="relay-url">{{ route('handleApi') }}</code>
                            <button class="btn btn-outline-secondary flex-shrink-0" onclick="copyRelayUrl(this)" title="Copy URL">
                                <i class="bi bi-copy" style="font-size:12px"></i>
                            </button>
                        </div>
                    </li>
                    <li>
                        Di setiap pembuatan transaksi di app kamu, sertakan domain identifier tadi sesuai
                        format provider-nya (lihat kartu di bawah): <code>custom_field1</code> untuk Midtrans,
                        <code>metadata.domain</code> untuk Xendit, <code>additional_info.domain</code> untuk DOKU.
                    </li>
                    <li>Relay mendeteksi provider dari header &amp; bentuk payload, lalu meneruskan payload ke target URL yang terdaftar
                        beserta header signature aslinya (<code>X-CALLBACK-TOKEN</code>, <code>X-Midtrans-Signature</code>,
                        <code>Client-Id</code>, <code>Signature</code>, dst).</li>
                    <li class="text-muted">Relay sendiri <strong>tidak</strong> memverifikasi signature — verifikasi tetap dilakukan aplikasi tujuan.</li>
                    <li>Semua aktivitas tercatat di halaman <a href="{{ route('panel.logs.index') }}">Logs</a>.</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Midtrans --}}
    <div class="col-12 col-lg-4">
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

            </div>
        </div>
    </div>

    {{-- Xendit --}}
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <h6 class="fw-semibold mb-3">
                    <span class="badge badge-xendit me-2">Xendit</span>Cara integrasi
                </h6>
                <hr>
                <p class="small text-muted mb-2">Saat membuat transaksi, tambahkan <code>metadata.domain</code> berisi domain identifier yang sudah didaftarkan. Relay otomatis mendeteksi <code>metadata.domain</code> di mana pun lokasinya dalam payload (mis. <code>data.metadata</code>, <code>qr_code.metadata</code>, dll):</p>
                <pre class="bg-light rounded p-3 small mb-3" style="overflow-x:auto"><code>$params = [
    'external_id' => 'INV-001',
    'amount'      => 100000,
    'metadata'    => [
        'domain' => 'example.com', // ← domain identifier
    ],
];</code></pre>

                <div class="alert alert-warning small mb-0 py-2 px-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Catatan untuk Xendit Invoice:</strong> pada <em>invoice mode</em>, <code>metadata</code> tidak ikut disertakan di payload webhook.
                    Sebagai gantinya, sisipkan domain di awal <code>external_id</code> dengan format <code>domain|invoice</code>
                    (mis. <code>example.com|INV-001</code>) — relay akan otomatis mem-parsing domain dari sana.
                </div>

            </div>
        </div>
    </div>

    {{-- DOKU --}}
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <h6 class="fw-semibold mb-3">
                    <span class="badge badge-doku me-2">DOKU</span>Cara integrasi
                </h6>
                <hr>
                <p class="small text-muted mb-2">Saat membuat transaksi, tambahkan <code>additional_info.domain</code> berisi domain identifier yang sudah didaftarkan. Sama seperti Xendit, relay mencarinya secara rekursif di mana pun letaknya dalam payload:</p>
                <pre class="bg-light rounded p-3 small mb-3" style="overflow-x:auto"><code>$params = [
    'order' => [
        'invoice_number' => 'INV-001',
        'amount'         => 100000,
    ],
    'additional_info' => [
        'domain' => 'example.com', // &larr; domain identifier
    ],
];</code></pre>

                <div class="alert alert-warning small mb-0 py-2 px-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Kalau <code>additional_info</code> tidak ikut dikirim balik</strong> oleh produk DOKU yang kamu pakai,
                    sisipkan domain di awal <code>invoice_number</code> dengan format <code>domain|invoice</code>
                    (mis. <code>example.com|INV-001</code>) — relay membaca bagian sebelum tanda <code>|</code>.
                </div>

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
                            <li>Domain identifier harus <strong>unik per provider</strong> dan konsisten di semua transaksi.</li>
                            <li>Jangan ubah domain identifier jika sudah ada transaksi berjalan.</li>
                            <li>Target URL harus <strong>dapat diakses publik</strong> (bukan localhost).</li>
                            <li>Target URL harus merespons dengan HTTP <strong>2xx</strong> agar log tercatat sukses.</li>
                        </ul>
                    </div>
                    <div class="col-12 col-md-6">
                        <ul class="small mb-0" style="line-height:2">
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
                                <td><span class="badge bg-secondary-subtle text-secondary">Domain tidak ditemukan</span></td>
                                <td>Nilai <code>custom_field1</code> / <code>metadata.domain</code> / <code>additional_info.domain</code> tidak terdaftar atau domain nonaktif</td>
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
