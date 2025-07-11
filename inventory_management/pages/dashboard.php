<?php
require_once '../includes/header.php';
?>

<section class="content-header">
    <div class="container-fluid">
        <h1>Dashboard</h1>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Statistik ringkas -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <!-- small box: Total Barang -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <?php
                        $totalBarang = getOne("SELECT COUNT(*) as total FROM barang")['total'] ?? 0;
                        ?>
                        <h3><?= $totalBarang ?></h3>
                        <p>Total Barang</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <a href="barang/index.php" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- small box: Total Supplier -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <?php
                        $totalSupplier = getOne("SELECT COUNT(*) as total FROM supplier")['total'] ?? 0;
                        ?>
                        <h3><?= $totalSupplier ?></h3>
                        <p>Total Supplier</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <a href="supplier/index.php" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- small box: Total Penjualan Hari Ini -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <?php
                        $today = date('Y-m-d');
                        $totalPenjualan = getOne("SELECT COUNT(*) as total FROM bayar WHERE tglbayar = ?", [$today])['total'] ?? 0;
                        ?>
                        <h3><?= $totalPenjualan ?></h3>
                        <p>Penjualan Hari Ini</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <a href="penjualan/index.php" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- small box: Stok Menipis -->
                <div class="small-box bg-danger">
                    <div class="inner">
                        <?php
                        $stokMenipis = getOne("SELECT COUNT(*) as total FROM barang WHERE sisa <= 5")['total'] ?? 0;
                        ?>
                        <h3><?= $stokMenipis ?></h3>
                        <p>Stok Menipis (<=5)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <a href="barang/index.php" class="small-box-footer">Periksa Stok <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Grafik penjualan dan pembelian -->
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Grafik Penjualan Bulanan</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartPenjualan"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Grafik Pembelian Bulanan</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartPembelian"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once '../includes/footer.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Fetch data for charts via AJAX or embed PHP data here
        const penjualanData = {
            labels: [],
            data: []
        };
        const pembelianData = {
            labels: [],
            data: []
        };

        // Example: Fetch data from server (to be implemented)
        // For now, dummy data
        penjualanData.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        penjualanData.data = [10, 20, 15, 30, 25, 40, 35, 50, 45, 60, 55, 70];

        pembelianData.labels = penjualanData.labels;
        pembelianData.data = [5, 15, 10, 20, 18, 25, 22, 30, 28, 35, 33, 40];

        const ctxPenjualan = document.getElementById('chartPenjualan').getContext('2d');
        const chartPenjualan = new Chart(ctxPenjualan, {
            type: 'line',
            data: {
                labels: penjualanData.labels,
                datasets: [{
                    label: 'Penjualan',
                    data: penjualanData.data,
                    borderColor: 'rgba(60,141,188,1)',
                    backgroundColor: 'rgba(60,141,188,0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        const ctxPembelian = document.getElementById('chartPembelian').getContext('2d');
        const chartPembelian = new Chart(ctxPembelian, {
            type: 'line',
            data: {
                labels: pembelianData.labels,
                datasets: [{
                    label: 'Pembelian',
                    data: pembelianData.data,
                    borderColor: 'rgba(40,167,69,1)',
                    backgroundColor: 'rgba(40,167,69,0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
</script>
