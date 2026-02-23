<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}
include '../koneksi.php';

// Query untuk grafik jurusan
$q_jurusan = mysqli_query($koneksi, "SELECT pilihan_jurusan, COUNT(*) as jumlah FROM jurusan_beasiswa GROUP BY pilihan_jurusan");
$jurusan = [];
$jumlah_jurusan = [];
while($r = mysqli_fetch_assoc($q_jurusan)){
  $jurusan[] = $r['pilihan_jurusan'];
  $jumlah_jurusan[] = $r['jumlah'];
}

// Query untuk grafik jenis kelamin
$q_gender = mysqli_query($koneksi, "SELECT jenis_kelamin, COUNT(*) as jumlah FROM pendaftaran_siswa GROUP BY jenis_kelamin");
$gender = [];
$jumlah_gender = [];
while($r = mysqli_fetch_assoc($q_gender)){
  $gender[] = $r['jenis_kelamin'];
  $jumlah_gender[] = $r['jumlah'];
}

// Query untuk grafik beasiswa
$q_beasiswa = mysqli_query($koneksi, "SELECT pilihan_beasiswa, COUNT(*) as jumlah FROM jurusan_beasiswa WHERE pilihan_beasiswa IS NOT NULL GROUP BY pilihan_beasiswa");
$beasiswa = [];
$jumlah_beasiswa = [];
while($r = mysqli_fetch_assoc($q_beasiswa)){
  $beasiswa[] = $r['pilihan_beasiswa'];
  $jumlah_beasiswa[] = $r['jumlah'];
}

// Query untuk grafik pendaftar per hari (7 hari terakhir)
$q_harian = mysqli_query($koneksi, "
    SELECT DATE(waktu_submit) as tanggal, COUNT(*) as jumlah 
    FROM pendaftaran_siswa 
    WHERE waktu_submit >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(waktu_submit) 
    ORDER BY tanggal
");
$tanggal = [];
$jumlah_harian = [];
while($r = mysqli_fetch_assoc($q_harian)){
  $tanggal[] = date('d/m', strtotime($r['tanggal']));
  $jumlah_harian[] = $r['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grafik Pendaftar | PPDB SMK UMAR MAS'UD</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    body {
      background: #f5f5f5;
      display: flex;
    }
    .sidebar {
      width: 250px;
      background: #004080;
      color: white;
      height: 100vh;
      padding: 20px;
      position: fixed;
    }
    .sidebar h2 {
      text-align: center;
      margin-bottom: 30px;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    .sidebar a {
      display: block;
      color: white;
      text-decoration: none;
      padding: 12px 15px;
      margin: 8px 0;
      border-radius: 5px;
      transition: background 0.3s;
    }
    .sidebar a:hover, .sidebar a.active {
      background: rgba(255,255,255,0.1);
    }
    .sidebar .logout {
      background: #dc3545;
      margin-top: 20px;
    }
    .sidebar .logout:hover {
      background: #c82333;
    }
    .content {
      margin-left: 250px;
      padding: 30px;
      width: calc(100% - 250px);
    }
    .header {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .header h1 {
      color: #004080;
      margin-bottom: 10px;
    }
    .charts-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
      gap: 20px;
      margin-bottom: 20px;
    }
    .chart-container {
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      height: 400px;
    }
    .chart-container h3 {
      color: #004080;
      margin-bottom: 15px;
      text-align: center;
    }
    .chart-wrapper {
      position: relative;
      height: 320px;
      width: 100%;
    }
    .stats-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }
    .stat-card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      text-align: center;
    }
    .stat-card h3 {
      color: #004080;
      font-size: 14px;
      margin-bottom: 10px;
    }
    .stat-card .number {
      font-size: 2em;
      font-weight: bold;
      color: #333;
    }
    .real-time-clock {
      font-size: 1.8em;
      font-weight: bold;
      color: #004080;
      font-family: 'Courier New', monospace;
    }
    .clock-label {
      font-size: 0.8em;
      color: #666;
      margin-top: 5px;
    }
    .refresh-info {
      font-size: 12px;
      color: #666;
      margin-top: 5px;
    }
    .auto-refresh {
      background: #e7f3ff;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 15px;
      font-size: 12px;
      border-left: 4px solid #004080;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="data_pendaftar.php">📋 Data Pendaftar</a>
    <a href="grafik.php" class="active">📊 Grafik</a>
    <a href="download.php">⬇️ Download Data</a>
    <a href="logout.php" class="logout">🚪 Logout</a>
  </div>

  <div class="content">
    <div class="header">
      <h1>Grafik dan Statistik Pendaftaran</h1>
      <p>Visualisasi data pendaftaran PPDB SMK UMAR MAS'UD</p>
    </div>

    <!-- Auto Refresh Info -->
    <div class="auto-refresh">
      <strong>🔄 Auto Refresh:</strong> Grafik akan diperbarui otomatis setiap 30 detik
    </div>

    <?php
    // Hitung total data
    $total_pendaftar = mysqli_fetch_array(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pendaftaran_siswa"))['total'];
    $total_jurusan = mysqli_fetch_array(mysqli_query($koneksi, "SELECT COUNT(DISTINCT pilihan_jurusan) as total FROM jurusan_beasiswa"))['total'];
    $total_beasiswa = mysqli_fetch_array(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jurusan_beasiswa WHERE pilihan_beasiswa IS NOT NULL"))['total'];
    ?>

    <div class="stats-cards">
      <div class="stat-card">
        <h3>Total Pendaftar</h3>
        <div class="number" id="totalPendaftar"><?= $total_pendaftar; ?></div>
      </div>
      <div class="stat-card">
        <h3>Jurusan Tersedia</h3>
        <div class="number" id="totalJurusan"><?= $total_jurusan; ?></div>
      </div>
      <div class="stat-card">
        <h3>Pemohon Beasiswa</h3>
        <div class="number" id="totalBeasiswa"><?= $total_beasiswa; ?></div>
      </div>
      <div class="stat-card">
        <h3>Update Terakhir</h3>
        <div class="real-time-clock" id="realTimeClock">
          <!-- Jam akan diisi oleh JavaScript -->
        </div>
        <div class="clock-label" id="dateDisplay">
          <!-- Tanggal akan diisi oleh JavaScript -->
        </div>
        <div class="refresh-info" id="lastRefresh">
          🔄 Auto refresh: <span id="countdown">30</span>s
        </div>
      </div>
    </div>

    <div class="charts-grid">
      <div class="chart-container">
        <h3>Pendaftar per Jurusan</h3>
        <div class="chart-wrapper">
          <canvas id="chartJurusan"></canvas>
        </div>
      </div>

      <div class="chart-container">
        <h3>Pendaftar per Jenis Kelamin</h3>
        <div class="chart-wrapper">
          <canvas id="chartGender"></canvas>
        </div>
      </div>

      <div class="chart-container">
        <h3>Pemohon Beasiswa</h3>
        <div class="chart-wrapper">
          <canvas id="chartBeasiswa"></canvas>
        </div>
      </div>

      <div class="chart-container">
        <h3>Pendaftar 7 Hari Terakhir</h3>
        <div class="chart-wrapper">
          <canvas id="chartHarian"></canvas>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Variabel global untuk menyimpan instance chart
    let charts = {
      jurusan: null,
      gender: null,
      beasiswa: null,
      harian: null
    };

    // Fungsi untuk update jam real-time
    function updateRealTimeClock() {
      const now = new Date();
      
      // Format waktu: HH:MM:SS (24 jam)
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');
      const timeString = `${hours}:${minutes}:${seconds}`;
      
      // Format tanggal: DD/MM/YYYY
      const day = String(now.getDate()).padStart(2, '0');
      const month = String(now.getMonth() + 1).padStart(2, '0');
      const year = now.getFullYear();
      const dateString = `${day}/${month}/${year}`;
      
      // Update elemen HTML
      document.getElementById('realTimeClock').textContent = timeString;
      document.getElementById('dateDisplay').textContent = dateString;
    }

    // Fungsi untuk countdown auto refresh
    let countdown = 30;
    function updateCountdown() {
      countdown--;
      document.getElementById('countdown').textContent = countdown;
      
      if (countdown <= 0) {
        countdown = 30;
        refreshData();
      }
    }

    // Fungsi untuk refresh data
    function refreshData() {
      console.log('🔄 Refreshing data...');
      
      // Update countdown
      document.getElementById('countdown').textContent = '0';
      
      // Show loading state
      document.querySelectorAll('.number').forEach(el => {
        el.innerHTML = '<span style="color: #999">Loading...</span>';
      });
      
      // Refresh page after a short delay to show loading state
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    }

    // Inisialisasi chart
    function initializeCharts() {
      // Chart Jurusan
      const ctxJurusan = document.getElementById('chartJurusan').getContext('2d');
      charts.jurusan = new Chart(ctxJurusan, {
        type: 'bar',
        data: {
          labels: <?= json_encode($jurusan); ?>,
          datasets: [{
            label: 'Jumlah Pendaftar',
            data: <?= json_encode($jumlah_jurusan); ?>,
            backgroundColor: [
              '#004080', '#007bff', '#17a2b8', '#28a745', '#ffc107', '#dc3545'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });

      // Chart Jenis Kelamin
      const ctxGender = document.getElementById('chartGender').getContext('2d');
      charts.gender = new Chart(ctxGender, {
        type: 'pie',
        data: {
          labels: <?= json_encode($gender); ?>,
          datasets: [{
            data: <?= json_encode($jumlah_gender); ?>,
            backgroundColor: ['#004080', '#e83e8c'],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });

      // Chart Beasiswa
      const ctxBeasiswa = document.getElementById('chartBeasiswa').getContext('2d');
      charts.beasiswa = new Chart(ctxBeasiswa, {
        type: 'doughnut',
        data: {
          labels: <?= json_encode($beasiswa); ?>,
          datasets: [{
            data: <?= json_encode($jumlah_beasiswa); ?>,
            backgroundColor: ['#28a745', '#17a2b8', '#ffc107'],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });

      // Chart Harian
      const ctxHarian = document.getElementById('chartHarian').getContext('2d');
      charts.harian = new Chart(ctxHarian, {
        type: 'line',
        data: {
          labels: <?= json_encode($tanggal); ?>,
          datasets: [{
            label: 'Pendaftar per Hari',
            data: <?= json_encode($jumlah_harian); ?>,
            backgroundColor: 'rgba(0, 64, 128, 0.1)',
            borderColor: '#004080',
            borderWidth: 2,
            tension: 0.4,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });
    }

    // Event listener untuk manual refresh
    document.addEventListener('keydown', function(e) {
      if (e.key === 'r' && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        refreshData();
      }
    });

    // Jalankan saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
      // Inisialisasi chart
      initializeCharts();
      
      // Update jam real-time
      updateRealTimeClock();
      setInterval(updateRealTimeClock, 1000);
      
      // Start countdown
      setInterval(updateCountdown, 1000);
      
      // Auto refresh setiap 30 detik
      setInterval(refreshData, 30000);
      
      // Info keyboard shortcut
      console.log('💡 Tips: Tekan Ctrl+R untuk refresh manual');
    });

    // Handle page visibility change (tab aktif/non-aktif)
    document.addEventListener('visibilitychange', function() {
      if (!document.hidden) {
        // Tab menjadi aktif, refresh data
        refreshData();
      }
    });
  </script>
</body>
</html>