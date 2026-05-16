@extends('layouts.app')
@section('title', 'Tambah Domain')
@section('content')
<div class="mb-4">
    <a href="{{ route('panel.domains.index') }}" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <h5 class="fw-semibold mb-0 mt-1">Tambah Domain</h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:600px">
    <div class="card-body p-4">
        <form action="{{ route('panel.domains.store') }}" method="POST" autocomplete="off">
            @csrf

            {{-- Step 1: Provider --}}
            <div class="mb-4">
                <label class="form-label fw-medium">Provider <span class="text-danger">*</span></label>
                <select name="provider" id="provider" class="form-select @error('provider') is-invalid @enderror"
                        onchange="onProviderChange(this.value)">
                    <option value="">-- Pilih provider dulu --</option>
                    <option value="midtrans" {{ old('provider') == 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                    <option value="xendit"   {{ old('provider') == 'xendit'   ? 'selected' : '' }}>Xendit</option>
                </select>
                @error('provider') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Fields muncul setelah pilih provider --}}
            <div id="form-fields" class="{{ old('provider') ? '' : 'd-none' }}">

                <div class="mb-3">
                    <label class="form-label fw-medium">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="Contoh: Toko A">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">Domain <span class="text-danger">*</span></label>
                    <input type="text" name="domain" id="domain"
                        class="form-control @error('domain') is-invalid @enderror"
                        value="{{ old('domain') }}" placeholder="contoh: domain.com">
                    <div id="domain-hint" class="form-text"></div>
                    @error('domain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">Target URL <span class="text-danger">*</span></label>
                    <input type="url" name="target_url"
                           class="form-control @error('target_url') is-invalid @enderror"
                           value="{{ old('target_url') }}"
                           placeholder="contoh: https://toko-a.com/webhook/payment">
                    @error('target_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium" id="secret-label">Secret Key <span class="text-danger">*</span></label>
                    <input type="text" name="secret_key" id="secret_key"
                           class="form-control @error('secret_key') is-invalid @enderror"
                           value="{{ old('secret_key') }}">
                    <div id="secret-hint" class="form-text"></div>
                    @error('secret_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Aktif</label>
                </div>

                <div class="border-top pt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark">Simpan</button>
                    <a href="{{ route('panel.domains.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
const hints = {
    midtrans: {
        domain: 'Nilai ini diisi di <code>custom_field1</code> saat membuat transaksi Midtrans.',
        secret: 'Server Key Midtrans — dipakai untuk validasi signature webhook.',
        secretPlaceholder: 'SB-Mid-server-xxxxxxxxxxxxxxxx',
    },
    xendit: {
        domain: 'Nilai ini diisi di <code>metadata.domain</code> saat membuat transaksi Xendit.',
        secret: 'Callback Token Xendit — ada di Settings → Developers → Webhook.',
        secretPlaceholder: 'xnd_xxxxxxxxxxxxxxxx',
    },
};

function onProviderChange(val) {
    const fields = document.getElementById('form-fields');
    const domainHint = document.getElementById('domain-hint'); // ← fix: slug-hint → domain-hint
    const secretHint = document.getElementById('secret-hint');
    const secretInput = document.getElementById('secret_key');

    if (!val) {
        fields.classList.add('d-none');
        return;
    }

    fields.classList.remove('d-none');
    domainHint.innerHTML = hints[val].domain; // ← fix: slug → domain
    secretHint.innerHTML = hints[val].secret;
    secretInput.placeholder = hints[val].secretPlaceholder;
}

document.addEventListener('DOMContentLoaded', () => {
    const val = document.getElementById('provider').value;
    if (val) onProviderChange(val);
});
</script>
@endsection
