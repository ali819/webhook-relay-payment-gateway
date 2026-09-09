@extends('layouts.app')
@section('title', 'Akun')
@section('content')
<div class="mb-4">
    <h5 class="fw-semibold mb-0">Akun</h5>
    <p class="text-muted small mb-0">Kelola kredensial login panel</p>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">Profil</h6>
                <div class="mb-3">
                    <div class="text-muted small">Nama</div>
                    <div>{{ auth()->user()->name }}</div>
                </div>
                <div>
                    <div class="text-muted small">Email</div>
                    <div>{{ auth()->user()->email }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-1">Ganti password</h6>
                <p class="text-muted small mb-3">
                    Setelah diganti, sesi di perangkat lain otomatis logout.
                </p>

                <form id="password-form" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Password saat ini</label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="current_password" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="#current_password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback d-block small text-danger" data-error="current_password"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-medium">Password baru</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="#password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Minimal 8 karakter, mengandung huruf dan angka.</div>
                        <div class="invalid-feedback d-block small text-danger" data-error="password"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-medium">Ulangi password baru</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="#password_confirmation">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dark" id="btn-save-password">Simpan password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    function clearErrors() {
        $('#password-form [data-error]').text('');
        $('#password-form .is-invalid').removeClass('is-invalid');
    }

    $('#password-form').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const btn = $('#btn-save-password').prop('disabled', true);

        $.post('{{ route('panel.account.password') }}', {
            _method:               'PUT',
            current_password:      $('#current_password').val(),
            password:              $('#password').val(),
            password_confirmation: $('#password_confirmation').val(),
        })
            .done(function (res) {
                $('#password-form')[0].reset();
                notify(res.message);
            })
            .fail(function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};
                    Object.keys(errors).forEach(function (field) {
                        $('#password-form [data-error="' + field + '"]').text(errors[field][0]);
                        $('#' + field).addClass('is-invalid');
                    });
                } else {
                    notify('Gagal mengganti password.', 'error');
                }
            })
            .always(() => btn.prop('disabled', false));
    });
});
</script>
@endpush
