// Fungsi validasi quantity refund untuk pegawai
function validateRefundQty(input) {
    const maxQty = parseInt(input.max);
    const currentQty = parseInt(input.value);
    const qtyError = document.getElementById('qtyError');
    const submitBtn = document.getElementById('submitRefundPegawai');

    if (currentQty > maxQty) {
        qtyError.classList.remove('d-none');
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.6';
    } else {
        qtyError.classList.add('d-none');
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
    }
}

// Fungsi validasi quantity refund untuk guest
function validateRefundGuestQty(input) {
    const maxQty = parseInt(input.max);
    const currentQty = parseInt(input.value);
    const qtyError = document.getElementById('guestQtyError');
    const submitBtn = document.getElementById('submitRefundGuest');

    if (currentQty > maxQty) {
        qtyError.classList.remove('d-none');
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.6';
    } else {
        qtyError.classList.add('d-none');
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
    }
}

// Inisialisasi ketika dokumen siap
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing modals');
    initModals();
    initSweetAlerts();
    initFormValidations();
    initButtonEventListeners();
});

// Fungsi untuk inisialisasi event listener pada tombol
function initButtonEventListeners() {
    // Event listener untuk tombol refund pegawai
    document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#refundModal"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const cartItemId = this.getAttribute('data-cart-item-id');
            const itemId = this.getAttribute('data-item-id');
            const itemName = this.getAttribute('data-item-name');
            const maxQty = this.getAttribute('data-max-qty');

            console.log('Refund Pegawai clicked:', { cartItemId, itemId, itemName, maxQty });

            document.getElementById('refundCartItemId').value = cartItemId;
            document.getElementById('refundItemId').value = itemId;
            document.getElementById('refundItemName').value = itemName;
            document.getElementById('refundQty').max = maxQty;
            document.getElementById('refundQty').value = 1;
            document.getElementById('maxQty').textContent = maxQty;

            // Reset validation state
            document.getElementById('qtyError').classList.add('d-none');
            document.getElementById('submitRefundPegawai').disabled = false;
            document.getElementById('submitRefundPegawai').style.opacity = '1';
        });
    });

    // Event listener untuk tombol refund guest
    document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#refundModalGuest"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const cartItemId = this.getAttribute('data-cart-item-id');
            const itemId = this.getAttribute('data-item-id');
            const itemName = this.getAttribute('data-item-name');
            const maxQty = this.getAttribute('data-max-qty');

            console.log('Refund Guest clicked:', { cartItemId, itemId, itemName, maxQty });

            document.getElementById('refundGuestCartItemId').value = cartItemId;
            document.getElementById('refundGuestItemId').value = itemId;
            document.getElementById('refundGuestItemName').value = itemName;
            document.getElementById('refundGuestQty').max = maxQty;
            document.getElementById('refundGuestQty').value = 1;
            document.getElementById('maxGuestQty').textContent = maxQty;

            // Reset validation state
            document.getElementById('guestQtyError').classList.add('d-none');
            document.getElementById('submitRefundGuest').disabled = false;
            document.getElementById('submitRefundGuest').style.opacity = '1';
        });
    });

    // Event listener untuk tombol edit pegawai
    document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#editModal"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const cartItemId = this.getAttribute('data-cart-item-id');
            const itemId = this.getAttribute('data-item-id');
            const qty = this.getAttribute('data-qty');

            console.log('Edit Pegawai clicked:', { cartItemId, itemId, qty });

            document.getElementById('editCartItemId').value = cartItemId;
            document.getElementById('editItemId').value = itemId;
            document.getElementById('editQty').value = qty;
        });
    });

    // Event listener untuk tombol edit guest
    document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#editModalGuest"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const guestCartItemId = this.getAttribute('data-guest-cart-item-id');
            const itemId = this.getAttribute('data-item-id');
            const qty = this.getAttribute('data-qty');

            console.log('Edit Guest clicked:', { guestCartItemId, itemId, qty });

            document.getElementById('editGuestCartItemId').value = guestCartItemId;
            document.getElementById('editGuestItemId').value = itemId;
            document.getElementById('editGuestQty').value = qty;
        });
    });
}

// Fungsi inisialisasi modal (sebagai backup)
function initModals() {
    console.log('Initializing modals...');

    // Refund Modal Pegawai
    const refundModal = document.getElementById('refundModal');
    if (refundModal) {
        const modalInstance = new bootstrap.Modal(refundModal);
        refundModal.addEventListener('show.bs.modal', function(event) {
            console.log('Refund Modal showing');
            const button = event.relatedTarget;
            if (button) {
                const cartItemId = button.getAttribute('data-cart-item-id');
                const itemId = button.getAttribute('data-item-id');
                const itemName = button.getAttribute('data-item-name');
                const maxQty = button.getAttribute('data-max-qty');

                document.getElementById('refundCartItemId').value = cartItemId || '';
                document.getElementById('refundItemId').value = itemId || '';
                document.getElementById('refundItemName').value = itemName || '';
                document.getElementById('refundQty').max = maxQty || 1;
                document.getElementById('refundQty').value = 1;
                document.getElementById('maxQty').textContent = maxQty || 0;
            }
        });
    }

    // Refund Modal Guest
    const refundModalGuest = document.getElementById('refundModalGuest');
    if (refundModalGuest) {
        const modalInstance = new bootstrap.Modal(refundModalGuest);
        refundModalGuest.addEventListener('show.bs.modal', function(event) {
            console.log('Refund Guest Modal showing');
            const button = event.relatedTarget;
            if (button) {
                const cartItemId = button.getAttribute('data-cart-item-id');
                const itemId = button.getAttribute('data-item-id');
                const itemName = button.getAttribute('data-item-name');
                const maxQty = button.getAttribute('data-max-qty');

                document.getElementById('refundGuestCartItemId').value = cartItemId || '';
                document.getElementById('refundGuestItemId').value = itemId || '';
                document.getElementById('refundGuestItemName').value = itemName || '';
                document.getElementById('refundGuestQty').max = maxQty || 1;
                document.getElementById('refundGuestQty').value = 1;
                document.getElementById('maxGuestQty').textContent = maxQty || 0;
            }
        });
    }

    // Edit Modal Pegawai
    const editModal = document.getElementById('editModal');
    if (editModal) {
        const modalInstance = new bootstrap.Modal(editModal);
        editModal.addEventListener('show.bs.modal', function(event) {
            console.log('Edit Modal showing');
            const button = event.relatedTarget;
            if (button) {
                const cartItemId = button.getAttribute('data-cart-item-id');
                const itemId = button.getAttribute('data-item-id');
                const qty = button.getAttribute('data-qty');

                document.getElementById('editCartItemId').value = cartItemId || '';
                document.getElementById('editItemId').value = itemId || '';
                document.getElementById('editQty').value = qty || 1;
            }
        });
    }

    // Edit Modal Guest
    const editModalGuest = document.getElementById('editModalGuest');
    if (editModalGuest) {
        const modalInstance = new bootstrap.Modal(editModalGuest);
        editModalGuest.addEventListener('show.bs.modal', function(event) {
            console.log('Edit Guest Modal showing');
            const button = event.relatedTarget;
            if (button) {
                const guestCartItemId = button.getAttribute('data-guest-cart-item-id');
                const itemId = button.getAttribute('data-item-id');
                const qty = button.getAttribute('data-qty');

                document.getElementById('editGuestCartItemId').value = guestCartItemId || '';
                document.getElementById('editGuestItemId').value = itemId || '';
                document.getElementById('editGuestQty').value = qty || 1;
            }
        });
    }

    // Reset form ketika modal ditutup
    const modals = ['refundModal', 'refundModalGuest', 'editModal', 'editModalGuest'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                console.log(`${modalId} hidden`);
                const form = this.querySelector('form');
                if (form) {
                    form.reset();
                    // Reset validation states
                    const qtyError = document.getElementById('qtyError');
                    const guestQtyError = document.getElementById('guestQtyError');
                    if (qtyError) qtyError.classList.add('d-none');
                    if (guestQtyError) guestQtyError.classList.add('d-none');

                    // Enable submit buttons
                    const submitBtns = form.querySelectorAll('button[type="submit"]');
                    submitBtns.forEach(btn => {
                        btn.disabled = false;
                        btn.style.opacity = '1';
                    });
                }
            });
        }
    });
}

// Fungsi inisialisasi SweetAlert
function initSweetAlerts() {
    // SweetAlert untuk pesan sukses/gagal dari session
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        confirmButtonText: 'Oke',
        confirmButtonColor: '#FF9800',
        background: '#fffaf4',
        iconColor: '#4CAF50'
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session('error') }}',
        confirmButtonText: 'Oke',
        confirmButtonColor: '#FF9800',
        background: '#fffaf4',
        iconColor: '#f44336'
    });
    @endif

    // SweetAlert untuk konfirmasi refund dan edit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const formType = this.getAttribute('action');

            if (formType && (formType.includes('refund') || formType.includes('update'))) {
                e.preventDefault();

                const actionType = formType.includes('refund') ? 'refund' : 'edit';
                const itemName = this.querySelector('input[readonly]')?.value || 'barang';

                Swal.fire({
                    title: `Konfirmasi ${actionType === 'refund' ? 'Refund' : 'Edit'}`,
                    text: `Apakah Anda yakin ingin ${actionType === 'refund' ? 'melakukan refund pada' : 'mengedit'} ${itemName}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#FF9800',
                    cancelButtonColor: '#6c757d',
                    background: '#fffaf4'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Tampilkan loading
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Sedang memproses permintaan Anda',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Submit form setelah konfirmasi
                        this.submit();
                    }
                });
            }
        });
    });
}

// Fungsi inisialisasi validasi form
function initFormValidations() {
    // SweetAlert untuk error validasi form
    document.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('invalid', function(e) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Data Tidak Lengkap',
                text: 'Harap lengkapi semua field yang wajib diisi',
                confirmButtonText: 'Oke',
                confirmButtonColor: '#FF9800',
                background: '#fffaf4'
            });
        });
    });
}

// Ekspor fungsi untuk penggunaan global jika diperlukan
window.validateRefundQty = validateRefundQty;
window.validateRefundGuestQty = validateRefundGuestQty;