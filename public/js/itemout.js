document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[id^='barcodeInput']").forEach(input => {
        input.addEventListener("keypress", async function (e) {
            if (e.key === "Enter") {
                const cartId = this.id.replace("barcodeInput", "");
                const barcode = this.value.trim();
                if (!barcode) return;

                try {
                    const res = await fetch(`/admin/itemout/scan/${cartId}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content
                        },
                        body: JSON.stringify({ barcode })
                    });

                    const data = await res.json();
                    if (!data.success) {
                        alert(data.message);
                        return;
                    }

                    // Tambahkan row baru ke tabel scan
                    const tableBody = document.querySelector(`#scanTable${cartId} tbody`);
                    const rowCount = tableBody.rows.length;

                    tableBody.insertAdjacentHTML("beforeend", `
                        <tr>
                            <td>${rowCount + 1}</td>
                            <td>${data.item.name}</td>
                            <td>${data.item.code}</td>
                            <td>${data.item.quantity ?? 1}</td>
                            <td>✅ Sudah discan</td>
                        </tr>
                    `);

                    this.value = "";

                    // Ubah tombol Scan -> Detail
                    const scanBtn = document.getElementById("scanBtn" + cartId);
                    if (scanBtn) {
                        scanBtn.classList.remove("btn-primary");
                        scanBtn.classList.add("btn-info");
                        scanBtn.textContent = "Detail";
                        scanBtn.setAttribute("data-bs-target", `#detailModal${cartId}`);
                    }

                } catch (err) {
                    console.error("Error scanning:", err);
                    alert("Terjadi kesalahan saat scan");
                }
            }
        });
    });
});
