<?php
session_start();

// KONFIGURASI DATABASE
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ppdb_smk_um');

// KONEKSI DATABASE
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// FUNGSI UTILITAS
function sanitize($data) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, htmlspecialchars(trim($data)));
}

// AMBIL DATA SISWA BERDASARKAN ID
$siswa_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($siswa_id <= 0) {
    die("ID tidak valid");
}

// QUERY UNTUK MENGAMBIL SEMUA DATA SESUAI STRUKTUR DATABASE
$query = "SELECT 
            ps.*, 
            als.provinsi, als.kota, als.kecamatan, als.alamat_lengkap,
            ow.nama_ayah, ow.pekerjaan_ayah, ow.nohp_ayah,
            ow.nama_ibu, ow.pekerjaan_ibu, ow.nohp_ibu,
            ow.nama_wali, ow.pekerjaan_wali, ow.nohp_wali,
            ak.asal_sekolah, ak.tahun_lulus, ak.rata_rata_raport,
            jb.pilihan_jurusan, jb.pilihan_beasiswa,
            dok.sk_lulus, dok.kk, dok.akta_lahir, dok.pas_foto, dok.ktp_ortu_wali, dok.sertifikat_prestasi
          FROM pendaftaran_siswa ps
          LEFT JOIN alamat_siswa als ON ps.id = als.siswa_id
          LEFT JOIN orangtua_wali ow ON ps.id = ow.siswa_id
          LEFT JOIN akademik ak ON ps.id = ak.siswa_id
          LEFT JOIN jurusan_beasiswa jb ON ps.id = jb.siswa_id
          LEFT JOIN dokumen dok ON ps.id = dok.siswa_id
          WHERE ps.id = '$siswa_id'";

$result = mysqli_query($koneksi, $query);
$siswa = mysqli_fetch_assoc($result);

if (!$siswa) {
    die("Data siswa tidak ditemukan");
}

// Format tanggal untuk ditampilkan
$tanggal_lahir = date('d-m-Y', strtotime($siswa['tanggal_lahir']));
$tanggal_daftar = date('d-m-Y', strtotime($siswa['waktu_submit']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Bukti Pendaftaran - PPDB SMK UMAR MAS'UD</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #004080;
        }
        .header h1 {
            color: #004080;
            margin-bottom: 10px;
            font-size: 24px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .badge-sukses {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            margin: 20px 0;
            font-weight: bold;
            font-size: 16px;
        }
        .info-pendaftaran {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #004080;
        }
        .section {
            margin-bottom: 25px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            page-break-inside: avoid;
        }
        .section h3 {
            color: #004080;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 18px;
        }
        .data-row {
            display: grid;
            grid-template-columns: 1fr 2fr;
            margin-bottom: 8px;
            padding: 3px 0;
        }
        .data-label {
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }
        .data-value {
            color: #666;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 12px;
        }
        .ttd-area {
            margin-top: 50px;
            text-align: right;
        }
        .ttd-label {
            margin-top: 60px;
        }
        .no-data {
            color: #999;
            font-style: italic;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
                border-radius: 0;
                padding: 15px;
            }
            .no-print {
                display: none;
            }
            .section {
                border: 1px solid #ccc;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>PPDB SMK UMAR MAS'UD</h1>
            <p>Form Pendaftaran Peserta Didik Baru</p>
            <div class="badge-sukses">PENDAFTARAN BERHASIL</div>
            
            <div class="info-pendaftaran">
                <div class="data-row">
                    <div class="data-label">No. Pendaftaran:</div>
                    <div class="data-value">PPDB-<?= str_pad($siswa['id'], 4, '0', STR_PAD_LEFT) ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Tanggal Pendaftaran:</div>
                    <div class="data-value"><?= $tanggal_daftar ?></div>
                </div>
            </div>
        </div>

        <!-- DATA SISWA -->
        <div class="section">
            <h3>Data Pribadi Siswa</h3>
            <div class="data-row">
                <div class="data-label">Nama Lengkap:</div>
                <div class="data-value"><?= htmlspecialchars($siswa['nama_lengkap']) ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">NIS:</div>
                <div class="data-value"><?= htmlspecialchars($siswa['nis']) ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Jenis Kelamin:</div>
                <div class="data-value"><?= htmlspecialchars($siswa['jenis_kelamin']) ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Agama:</div>
                <div class="data-value"><?= htmlspecialchars($siswa['agama']) ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Tanggal Lahir:</div>
                <div class="data-value"><?= $tanggal_lahir ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">No. WhatsApp:</div>
                <div class="data-value"><?= htmlspecialchars($siswa['no_hp']) ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Email:</div>
                <div class="data-value"><?= htmlspecialchars($siswa['email']) ?></div>
            </div>
        </div>

        <!-- ALAMAT -->
        <div class="section">
            <h3>Data Alamat</h3>
            <div class="data-row">
                <div class="data-label">Provinsi:</div>
                <div class="data-value"><?= !empty($siswa['provinsi']) ? htmlspecialchars($siswa['provinsi']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Kota/Kabupaten:</div>
                <div class="data-value"><?= !empty($siswa['kota']) ? htmlspecialchars($siswa['kota']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Kecamatan:</div>
                <div class="data-value"><?= !empty($siswa['kecamatan']) ? htmlspecialchars($siswa['kecamatan']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Alamat Lengkap:</div>
                <div class="data-value"><?= !empty($siswa['alamat_lengkap']) ? htmlspecialchars($siswa['alamat_lengkap']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
        </div>

        <!-- ORANG TUA -->
        <div class="section">
            <h3>Data Orang Tua/Wali</h3>
            <div class="data-row">
                <div class="data-label">Nama Ayah:</div>
                <div class="data-value"><?= !empty($siswa['nama_ayah']) ? htmlspecialchars($siswa['nama_ayah']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Pekerjaan Ayah:</div>
                <div class="data-value"><?= !empty($siswa['pekerjaan_ayah']) ? htmlspecialchars($siswa['pekerjaan_ayah']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">No. HP Ayah:</div>
                <div class="data-value"><?= !empty($siswa['nohp_ayah']) ? htmlspecialchars($siswa['nohp_ayah']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Nama Ibu:</div>
                <div class="data-value"><?= !empty($siswa['nama_ibu']) ? htmlspecialchars($siswa['nama_ibu']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Pekerjaan Ibu:</div>
                <div class="data-value"><?= !empty($siswa['pekerjaan_ibu']) ? htmlspecialchars($siswa['pekerjaan_ibu']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">No. HP Ibu:</div>
                <div class="data-value"><?= !empty($siswa['nohp_ibu']) ? htmlspecialchars($siswa['nohp_ibu']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <?php if (!empty($siswa['nama_wali'])): ?>
            <div class="data-row">
                <div class="data-label">Nama Wali:</div>
                <div class="data-value"><?= htmlspecialchars($siswa['nama_wali']) ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Pekerjaan Wali:</div>
                <div class="data-value"><?= !empty($siswa['pekerjaan_wali']) ? htmlspecialchars($siswa['pekerjaan_wali']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">No. HP Wali:</div>
                <div class="data-value"><?= !empty($siswa['nohp_wali']) ? htmlspecialchars($siswa['nohp_wali']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- AKADEMIK -->
        <div class="section">
            <h3>Data Akademik</h3>
            <div class="data-row">
                <div class="data-label">Asal Sekolah:</div>
                <div class="data-value"><?= !empty($siswa['asal_sekolah']) ? htmlspecialchars($siswa['asal_sekolah']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Tahun Lulus:</div>
                <div class="data-value"><?= !empty($siswa['tahun_lulus']) && $siswa['tahun_lulus'] != '0000' ? htmlspecialchars($siswa['tahun_lulus']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Rata-rata Nilai Raport:</div>
                <div class="data-value"><?= !empty($siswa['rata_rata_raport']) && $siswa['rata_rata_raport'] > 0 ? htmlspecialchars($siswa['rata_rata_raport']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
        </div>

        <!-- JURUSAN -->
        <div class="section">
            <h3>Pilihan Jurusan & Beasiswa</h3>
            <div class="data-row">
                <div class="data-label">Pilihan Jurusan:</div>
                <div class="data-value"><?= !empty($siswa['pilihan_jurusan']) ? htmlspecialchars($siswa['pilihan_jurusan']) : '<span class="no-data">Belum diisi</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Pilihan Beasiswa:</div>
                <div class="data-value"><?= !empty($siswa['pilihan_beasiswa']) ? htmlspecialchars($siswa['pilihan_beasiswa']) : '<span class="no-data">Tidak Mengajukan</span>' ?></div>
            </div>
        </div>

        <!-- DOKUMEN -->
        <div class="section">
            <h3>Dokumen yang Diupload</h3>
            <div class="data-row">
                <div class="data-label">Surat Keterangan Lulus/Ijazah:</div>
                <div class="data-value"><?= !empty($siswa['sk_lulus']) ? '✓ Terupload' : '<span class="no-data">Belum diupload</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Kartu Keluarga:</div>
                <div class="data-value"><?= !empty($siswa['kk']) ? '✓ Terupload' : '<span class="no-data">Belum diupload</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Akta Lahir:</div>
                <div class="data-value"><?= !empty($siswa['akta_lahir']) ? '✓ Terupload' : '<span class="no-data">Belum diupload</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Pas Foto 3x4:</div>
                <div class="data-value"><?= !empty($siswa['pas_foto']) ? '✓ Terupload' : '<span class="no-data">Belum diupload</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">KTP Orang Tua/Wali:</div>
                <div class="data-value"><?= !empty($siswa['ktp_ortu_wali']) ? '✓ Terupload' : '<span class="no-data">Belum diupload</span>' ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Sertifikat Prestasi:</div>
                <div class="data-value"><?= !empty($siswa['sertifikat_prestasi']) ? '✓ Terupload' : '<span class="no-data">Tidak ada</span>' ?></div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p><strong>Terima kasih telah mendaftar di SMK UMAR MAS'UD. Data Anda telah berhasil disimpan.</strong></p>
            <p>Silakan tunggu informasi lebih lanjut melalui email atau WhatsApp.</p>
            
            <div class="ttd-area">
                <div class="ttd-label">
                    <p>Sangkapura, <?= date('d F Y') ?></p>
                    <br><br><br>
                    <p><strong>Panitia PPDB</strong></p>
                    <p>SMK Umar Mas'ud</p>
                </div>
            </div>
        </div>

        <!-- TOMBOL CETAK -->
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" class="btn btn-primary" style="padding: 10px 20px; background: #004080; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px;">
                🖨️ Cetak Bukti
            </button>
            <a href="../index.php" class="btn btn-secondary" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin: 5px;">
                ← Kembali ke Beranda
            </a>
            <a href="ppdb.php?reset=1" class="btn btn-info" style="padding: 10px 20px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px; margin: 5px;">
                📝 Daftar Lagi
            </a>
        </div>
    </div>

    <script>
        // Auto print ketika halaman dimuat (opsional)
        window.onload = function() {
            // Uncomment baris berikut jika ingin auto print
            // setTimeout(() => { window.print(); }, 1000);
        };
    </script>
</body>
</html>