document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('trafficChart').getContext('2d');

    // === Default: tampilkan data mingguan ===
    fetch(`/admin/dashboard/data?range=week`)
        .then(res => res.json())
        .then(data => {
            window.trafficChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Barang Keluar',
                            data: data.keluar,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 1000, easing: 'easeInOutQuad' },
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        });

    // === Fungsi update chart berdasarkan range ===
    window.updateChart = function(range) {
        fetch(`/admin/dashboard/data?range=${range}`)
            .then(res => res.json())
            .then(data => {
                trafficChart.data.labels = data.labels;
                trafficChart.data.datasets[0].data = data.keluar;
                trafficChart.update();
            });
    };
});
