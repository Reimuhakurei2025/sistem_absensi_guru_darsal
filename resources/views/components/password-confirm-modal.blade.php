@props([
    'id'         => 'password-confirm-modal',
    'title'      => 'Konfirmasi Password',
    'message'    => 'Masukkan password Anda untuk melanjutkan.',
    'formId'     => 'target-form',           // ID form yang akan di-submit
    'inputName'  => 'kepsek_password',        // nama input hidden di form
    'buttonText' => 'Konfirmasi',
    'buttonClass' => 'btn-primary',
])

{{--
    CARA PAKAI:

    1) Pasang form dengan ID, dan tombol submit yang TIDAK type="submit" (type="button"
       dengan onclick membuka modal):

       <form id="form-tambah-guru" method="POST" action="...">
           @csrf
           ... input lainnya ...
           <input type="hidden" name="kepsek_password" id="form-tambah-guru-password" value="">
           <button type="button" onclick="openPasswordModal('form-tambah-guru')" class="btn-primary">
               Simpan Guru
           </button>
       </form>

    2) Pasang modal di akhir halaman (sekali saja per halaman):

       <x-password-confirm-modal />

    Modal akan minta password, lalu inject ke hidden input form, lalu submit form.
--}}

<div id="{{ $id }}"
     class="fixed inset-0 z-50 hidden items-center justify-center px-4
            bg-black/50 backdrop-blur-sm"
     role="dialog" aria-modal="true">

    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 transform transition-all">

        {{-- Header --}}
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 id="{{ $id }}-title" class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                <p id="{{ $id }}-message" class="text-sm text-gray-600 mt-1">{{ $message }}</p>
            </div>
        </div>

        {{-- Input password --}}
        <div class="mb-4">
            <label for="{{ $id }}-input" class="form-label">Password Anda</label>
            <input type="password"
                   id="{{ $id }}-input"
                   class="form-input"
                   placeholder="••••••••"
                   autocomplete="current-password">
            <p id="{{ $id }}-error" class="text-xs text-red-600 mt-1.5 hidden"></p>
        </div>

        {{-- Actions --}}
        <div class="flex gap-2 justify-end">
            <button type="button"
                    onclick="closePasswordModal('{{ $id }}')"
                    class="btn-secondary">
                Batal
            </button>
            <button type="button"
                    id="{{ $id }}-confirm"
                    class="{{ $buttonClass }}">
                {{ $buttonText }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
/**
 * Password Confirmation Modal Helper
 *
 * Workflow:
 *   1. User klik tombol aksi → openPasswordModal(formId)
 *   2. Modal terbuka, user masukkan password
 *   3. User klik "Konfirmasi" → password di-inject ke hidden input form → form submit
 */

let _activeFormId = null;

function openPasswordModal(formId, modalId = '{{ $id }}', inputName = '{{ $inputName }}') {
    _activeFormId = formId;
    const modal = document.getElementById(modalId);
    const input = document.getElementById(modalId + '-input');
    const error = document.getElementById(modalId + '-error');

    error.classList.add('hidden');
    error.textContent = '';
    input.value = '';

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(() => input.focus(), 100);
}

function closePasswordModal(modalId = '{{ $id }}') {
    const modal = document.getElementById(modalId);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    _activeFormId = null;
}

document.addEventListener('DOMContentLoaded', () => {
    const modalId    = '{{ $id }}';
    const inputName  = '{{ $inputName }}';
    const confirmBtn = document.getElementById(modalId + '-confirm');
    const input      = document.getElementById(modalId + '-input');
    const error      = document.getElementById(modalId + '-error');

    const submitForm = () => {
        const password = input.value.trim();

        if (!password) {
            error.textContent = 'Password tidak boleh kosong';
            error.classList.remove('hidden');
            return;
        }

        if (password.length < 6) {
            error.textContent = 'Password minimal 6 karakter';
            error.classList.remove('hidden');
            return;
        }

        if (!_activeFormId) {
            error.textContent = 'Form target tidak ditemukan';
            error.classList.remove('hidden');
            return;
        }

        const form = document.getElementById(_activeFormId);
        if (!form) {
            error.textContent = 'Form #' + _activeFormId + ' tidak ditemukan';
            error.classList.remove('hidden');
            return;
        }

        // Inject password ke hidden input. Buat input jika belum ada.
        let hidden = form.querySelector(`input[name="${inputName}"]`);
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = inputName;
            form.appendChild(hidden);
        }
        hidden.value = password;

        // Submit form
        form.submit();
    };

    confirmBtn?.addEventListener('click', submitForm);
    input?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            submitForm();
        }
    });
});
</script>
@endpush
