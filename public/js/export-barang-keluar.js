/**
 * Export Data Barang Keluar - JavaScript File
 *
 * Handles:
 * - Letterhead selection preview
 * - Excel export
 * - PDF validation
 */

document.addEventListener('DOMContentLoaded', function() {

    // DOM Elements
    const kopSelect = document.getElementById('kop_surat');
    const kopHidden = document.getElementById('kop_surat_hidden');
    const previewDiv = document.getElementById('kop_preview_full');
    const excelButtons = document.querySelectorAll('.export-excel');
    const pdfForm = document.getElementById('pdf-form');

    // Reset state
    if (kopSelect) kopSelect.selectedIndex = 0;
    if (kopHidden) kopHidden.value = '';

    /**
     * ============================
     *  LETTERHEAD PREVIEW
     * ============================
     */
    kopSelect?.addEventListener('change', () => {

        const selected = kopSelect.options[kopSelect.selectedIndex];

        if (!selected.value) {
            previewDiv.innerHTML = `<em>Pilih kop surat untuk melihat preview lengkap</em>`;
            kopHidden.value = '';
            return;
        }

        previewDiv.innerHTML = `
            <table style="width:100%; border:none;">
                <tr>
                    <td style="width:120px; text-align:center;">
                        <img src="${selected.dataset.logo}"
                             style="width:90px; height:100px; object-fit:contain;">
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        <div style="font-size:14px; font-weight:600;">
                            ${selected.dataset.instansi.toUpperCase()}
                        </div>
                        <div style="font-size:18px; font-weight:900; margin-top:4px;">
                            ${selected.dataset.unit.toUpperCase()}
                        </div>
                        <div style="font-size:13px; margin-top:4px;">
                            ${selected.dataset.alamat}<br>
                            Telepon: ${selected.dataset.telepon}<br>
                            Website: ${selected.dataset.website} |
                            Email: ${selected.dataset.email}<br>
                            ${selected.dataset.kota}
                        </div>
                    </td>
                    <td style="width:120px;"></td>
                </tr>
            </table>
        `;

        kopHidden.value = selected.value;
    });

    /**
     * ============================
     *  EXPORT EXCEL
     * ============================
     */
    excelButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            const start = this.getAttribute('data-start-date');
            const end = this.getAttribute('data-end-date');
            const kopId = kopSelect?.value;
            const baseRoute = this.getAttribute('data-route'); // Ambil route dari attribute

            if (!kopId) {
                alert('Silakan pilih kop surat terlebih dahulu sebelum export Excel.');
                return;
            }

            // Gunakan route name dengan parameter query
            const url = `${baseRoute}?start_date=${start}&end_date=${end}&kop_surat=${kopId}`;
            window.location.href = url;
        });
    });

    /**
     * ============================
     *  EXPORT PDF
     * ============================
     */
    pdfForm?.addEventListener('submit', function(e) {
        if (!kopSelect.value) {
            e.preventDefault();
            alert('Silakan pilih kop surat terlebih dahulu sebelum export PDF.');
            return false;
        }
    });

});
