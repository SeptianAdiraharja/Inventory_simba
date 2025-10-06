document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById("trafficChart").getContext("2d");

    // === Default tampilkan data mingguan ===
    fetch(`/admin/dashboard/data?range=week`)
        .then((res) => res.json())
        .then((data) => {
            window.trafficChart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: "Barang Keluar",
                            data: data.keluar,
                            borderColor: "rgba(255, 99, 132, 1)",
                            backgroundColor: "rgba(255, 99, 132, 0.15)",
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointBackgroundColor: "rgba(255, 99, 132, 1)",
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 1000, easing: "easeInOutQuad" },
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: "#333",
                                font: { size: 12, weight: "bold" },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: "#666" },
                            grid: { color: "rgba(200,200,200,0.2)" },
                        },
                        x: {
                            ticks: { color: "#666" },
                            grid: { display: false },
                        },
                    },
                },
            });
        });

    // === Fungsi untuk update chart berdasarkan periode ===
    window.updateChart = function (range) {
        fetch(`/admin/dashboard/data?range=${range}`)
            .then((res) => res.json())
            .then((data) => {
                trafficChart.data.labels = data.labels;
                trafficChart.data.datasets[0].data = data.keluar;
                trafficChart.update();
            });
    };
});
