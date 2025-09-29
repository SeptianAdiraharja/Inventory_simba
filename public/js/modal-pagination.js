// === Pagination AJAX di dalam modal ===
$(document).on('click', '#modal-barang_keluar .pagination a, #modal-request .pagination a', function(e) {
    e.preventDefault(); // cegah reload full page
    var url = $(this).attr('href'); // ambil url dari link
    var modal = $(this).closest('.modal');
    var modalBody = modal.find('.modal-body');

    $.get(url, function(data) {
        // Ambil ulang hanya isi modal-body dari response
        var newContent = $(data).find('#' + modal.attr('id') + ' .modal-body').html();
        modalBody.html(newContent);
    });
});

// === Perbaikan backdrop modal ===
document.addEventListener('hidden.bs.modal', function () {
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
});
