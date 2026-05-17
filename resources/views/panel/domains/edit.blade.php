@extends('layouts.app')
@section('title', 'Edit Domain')
@section('content')
<div class="mb-4">
    <a href="{{ route('panel.domains.index') }}" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <h5 class="fw-semibold mb-0 mt-1">Edit Domain</h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:600px">
    <div class="card-body p-4">
        <form action="{{ route('panel.domains.update', $domain) }}" method="POST" autocomplete="off">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="form-label fw-medium">Provider <span class="text-danger">*</span></label>
                <select name="provider" id="provider" class="form-select">
                    <option value="midtrans" {{ old('provider', $domain->provider) == 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                    <option value="xendit"   {{ old('provider', $domain->provider) == 'xendit'   ? 'selected' : '' }}>Xendit</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Target URL <span class="text-danger">*</span></label>
                <input type="url" name="target_url" id="target_url"
                       class="form-control @error('target_url') is-invalid @enderror"
                       value="{{ old('target_url', $domain->target_url) }}"
                       oninput="previewDomain(this.value)">
                <div id="domain-preview" class="form-text"></div>
                @if($domain->logs()->exists())
                    <div class="form-text text-warning mt-1">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Domain ini sudah punya log. Mengubah URL akan mengupdate domain identifier secara otomatis.
                    </div>
                @endif
                @error('target_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                       {{ old('is_active', $domain->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Aktif</label>
            </div>

            <div class="border-top pt-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Perbarui</button>
                <a href="{{ route('panel.domains.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>

        </form>
    </div>
</div>

<script>
function previewDomain(url) {
    const preview = document.getElementById('domain-preview');
    try {
        const parsed = new URL(url);
        preview.innerHTML = `Domain terdeteksi: <strong>${parsed.hostname}</strong>`;
        preview.className = 'form-text text-success';
    } catch {
        preview.innerHTML = '';
        preview.className = 'form-text';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const url = document.getElementById('target_url').value;
    if (url) previewDomain(url);
});
</script>
@endsection
