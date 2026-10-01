import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Grafik Tren Biaya Payroll 6 Bulan
    const payrollCanvas = document.getElementById('chart-payroll-trend');
    if (payrollCanvas && payrollCanvas.dataset.payroll) {
        try {
            const payrollData = JSON.parse(payrollCanvas.dataset.payroll);
            const labels = payrollData.map(item => item.label);
            const gross = payrollData.map(item => item.gross);
            const net = payrollData.map(item => item.net);

            new Chart(payrollCanvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Gaji Bruto (Gross)',
                            data: gross,
                            backgroundColor: 'rgba(14, 165, 233, 0.85)', // sky-500
                            borderRadius: 6,
                        },
                        {
                            label: 'Gaji Bersih (Net)',
                            data: net,
                            backgroundColor: 'rgba(16, 185, 129, 0.85)', // emerald-500
                            borderRadius: 6,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { family: 'sans-serif', size: 12, weight: 'bold' },
                                boxWidth: 14,
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return 'Rp ' + (value / 1000000) + ' Jt';
                                },
                            },
                        },
                    },
                },
            });
        } catch (e) {
            console.error('Error rendering payroll chart:', e);
        }
    }

    // 2. Grafik Rasio Kehadiran Hari Ini (Doughnut)
    const attendanceCanvas = document.getElementById('chart-attendance-donut');
    if (attendanceCanvas && attendanceCanvas.dataset.attendance) {
        try {
            const att = JSON.parse(attendanceCanvas.dataset.attendance);
            const dataValues = [
                att.present_ontime || 0,
                att.present_late || 0,
                att.leave_permission || 0,
                att.sick || 0,
                att.absent || 0,
            ];

            new Chart(attendanceCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Tepat Waktu', 'Terlambat', 'Cuti / Izin', 'Sakit', 'Mangkir / Alpa'],
                    datasets: [{
                        data: dataValues,
                        backgroundColor: [
                            '#10B981', // emerald-500
                            '#F59E0B', // amber-500
                            '#0284C7', // sky-600
                            '#6366F1', // indigo-500
                            '#F43F5E', // rose-500
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 12,
                                font: { size: 11 },
                            },
                        },
                    },
                    cutout: '65%',
                },
            });
        } catch (e) {
            console.error('Error rendering attendance chart:', e);
        }
    }

    // 3. Distribusi Headcount per Departemen (Bar Horizontal)
    const deptCanvas = document.getElementById('chart-department-headcount');
    if (deptCanvas && deptCanvas.dataset.departments) {
        try {
            const depts = JSON.parse(deptCanvas.dataset.departments);
            const labels = depts.map(d => d.name);
            const counts = depts.map(d => d.count);

            new Chart(deptCanvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Karyawan',
                        data: counts,
                        backgroundColor: 'rgba(11, 30, 54, 0.85)', // navy/deep ocean
                        borderRadius: 6,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                        },
                    },
                },
            });
        } catch (e) {
            console.error('Error rendering department chart:', e);
        }
    }
});
