@extends('layouts.index')
@section('title', 'Panduan Pemakaian Aplikasi')
@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="ri ri-book-open-line display-6 me-3"></i>
                        <div>
                            <h1 class="h2 mb-1">Panduan Pemakaian Aplikasi</h1>
                            <p class="mb-0">Petunjuk lengkap untuk menggunakan sistem manajemen barang</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs nav-justified" id="panduanTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="request-tab" data-bs-toggle="tab" data-bs-target="#request" type="button" role="tab">
                        <i class="ri ri-file-list-3-line me-2"></i>Permintaan Barang
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="scan-tab" data-bs-toggle="tab" data-bs-target="#scan" type="button" role="tab">
                        <i class="ri ri-qr-scan-2-line me-2"></i>Scan QR Code
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="transaksi-tab" data-bs-toggle="tab" data-bs-target="#transaksi" type="button" role="tab">
                        <i class="bi-pencil-square me-2"></i>Data Transaksi
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rusak-tab" data-bs-toggle="tab" data-bs-target="#rusak" type="button" role="tab">
                        <i class="ri ri-close-circle-line me-2"></i>Barang Rusak
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="export-tab" data-bs-toggle="tab" data-bs-target="#export" type="button" role="tab">
                        <i class="ri ri-download-2-line me-2"></i>Export Data
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="guest-tab" data-bs-toggle="tab" data-bs-target="#guest" type="button" role="tab">
                        <i class="ri ri-user-line me-2"></i>Manajemen Tamu & Pegawai
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="row">
        <div class="col-12">
            <div class="tab-content" id="panduanContent">

                <!-- Tab 1: Permintaan Barang -->
                <div class="tab-pane fade show active" id="request" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h3 class="card-title mb-0">
                                <i class="ri ri-file-list-3-line text-primary me-2"></i>
                                Mengelola Permintaan Barang dari Pegawai
                            </h3>
                        </div>

                        <div class="card-body">

                            <!-- Bagian Langkah-Langkah -->
                            <div class="row mb-4">
                                <h4 class="text-primary">Langkah-langkah:</h4>
                                <div class="col-md-12">
                                    <ol class="list-group list-group-numbered">
                                        <li class="list-group-item border-0 px-0">Buka halaman <strong>Permintaan</strong> dari menu Admin</li>
                                        <li class="list-group-item border-0 px-0">Anda akan melihat daftar permintaan dari pegawai</li>
                                        <li class="list-group-item border-0 px-0">
                                            Klik tombol <span class="badge bg-primary">Lihat Semua Barang</span> untuk melihat detail barang yang diminta
                                        </li>
                                        <li class="list-group-item border-0 px-0">Di halaman detail, Anda dapat:
                                            <ul class="mt-2">
                                                <li>Setujui semua barang sekaligus</li>
                                                <li>Tolak semua barang sekaligus</li>
                                                <li>Setujui atau tolak per barang</li>
                                            </ul>
                                        </li>
                                        <li class="list-group-item border-0 px-0">
                                            Jika menolak permintaan, isi alasan penolakan pada kotak yang muncul
                                        </li>
                                        <li class="list-group-item border-0 px-0">
                                            Setelah semua barang diproses, sistem akan mengarahkan ke halaman Scan QR
                                        </li>
                                    </ol>
                                </div>
                            </div>

                            <!-- Carousel di Bagian Bawah -->
                            <div class="row justify-content-center">
                                <div class="col-12 text-center">
                                    <div id="carouselRequest" class="carousel slide mx-auto" 
                                        data-bs-ride="carousel" data-bs-interval="3500">

                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="{{ asset('assets/img/panduan_admin/request/request.png') }}" 
                                                    class="d-block w-100 rounded shadow" alt="Halaman Permintaan">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/request/requestapprove.png') }}" 
                                                    class="d-block w-100 rounded shadow" alt="Approve Permintaan">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/request/requestrejectall.png') }}" 
                                                    class="d-block w-100 rounded shadow" alt="Reject Semua">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/request/requestrejectmodal.png') }}" 
                                                    class="d-block w-100 rounded shadow" alt="Modal Reject">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/request/requestrejectresult.png') }}" 
                                                    class="d-block w-100 rounded shadow" alt="Hasil Reject">
                                            </div>
                                        </div>

                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselRequest" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselRequest" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>

                                    <p class="text-muted small mt-2">Contoh tampilan halaman permintaan</p>
                                </div>
                            </div>

                            <div class="alert alert-info mt-4">
                                <i class="ri-information-line me-2"></i>
                                <strong>Tips:</strong> Pastikan memeriksa ketersediaan barang sebelum menyetujui permintaan.
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Tab 2: Scan QR Code -->
                <div class="tab-pane fade" id="scan" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h3 class="card-title mb-0">
                                <i class="ri ri-qr-scan-2-line text-primary me-2"></i>
                                Melakukan Scan QR Code Barang
                            </h3>
                        </div>

                        <div class="card-body">

                            <!-- Bagian Langkah-Langkah -->
                            <div class="row mb-4">
                                <h4 class="text-primary">Langkah-langkah:</h4>
                                <div class="col-md-12">
                                    <ol class="list-group list-group-numbered">
                                        <li class="list-group-item border-0 px-0">Buka halaman <strong>Scan QR</strong> dari menu Admin</li>
                                        <li class="list-group-item border-0 px-0">Arahkan kamera perangkat ke QR Code pada barang</li>
                                        <li class="list-group-item border-0 px-0">Tunggu hingga sistem membaca kode QR</li>
                                        <li class="list-group-item border-0 px-0">Informasi barang akan muncul secara otomatis</li>
                                        <li class="list-group-item border-0 px-0">Konfirmasi pengeluaran barang</li>
                                        <li class="list-group-item border-0 px-0">Sistem akan mencatat barang keluar secara otomatis</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- Carousel di Bagian Bawah -->
                            <div class="row justify-content-center">
                                <div class="col-12 text-center">
                                    <div id="carouselScan" class="carousel slide mx-auto" data-bs-ride="carousel" data-bs-interval="3500">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="{{ asset('assets/img/panduan_admin/itemout/itemout.png') }}" class="d-block w-100 rounded shadow" alt="Halaman Item Out">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/itemout/itemoutscan.png') }}" class="d-block w-100 rounded shadow" alt="Scan QR">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/itemout/itemoutpopup.png') }}" class="d-block w-100 rounded shadow" alt="Popup Scan">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/itemout/itemoutsimpan.png') }}" class="d-block w-100 rounded shadow" alt="Simpan Item">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/itemout/itemoutvalidasi.png') }}" class="d-block w-100 rounded shadow" alt="Validasi">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/itemout/itemoutresult.png') }}" class="d-block w-100 rounded shadow" alt="Hasil">
                                            </div>
                                        </div>

                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselScan" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselScan" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>

                                    <p class="text-muted small mt-2">Contoh proses scanning QR Code</p>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="ri-information-line me-2"></i>
                                <strong>Tips:</strong> Pastikan pencahayaan cukup dan QR Code tidak rusak untuk proses scanning yang lancar.
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Tab 3: Data Transaksi -->
                <div class="tab-pane fade" id="transaksi" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h3 class="card-title mb-0">
                                <i class="bi-pencil-square text-primary me-2"></i>
                                Mengelola Data Transaksi
                            </h3>
                        </div>
                        <div class="card-body">
                            
                            <!-- Bagian Card Edit & Refund -->
                            <div class="row mb-4">
                                <h4 class="text-primary">Fitur yang tersedia:</h4>
                                <div class="col-md-6 mt-3">
                                    <div class="card border-primary h-100">
                                        <div class="card-body">
                                            <h5 class="card-title text-primary">Edit Barang</h5>
                                            <p class="card-text">Digunakan ketika pegawai ingin mengganti barang tanpa pengajuan ulang</p>
                                            <ol class="small">
                                                <li>Cari transaksi yang ingin diubah</li>
                                                <li>Klik tombol <span class="badge bg-warning text-dark">Edit</span></li>
                                                <li>Pilih barang pengganti</li>
                                                <li>Simpan perubahan</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <div class="card border-success h-100">
                                        <div class="card-body">
                                            <h5 class="card-title text-success">Refund Barang</h5>
                                            <p class="card-text">Digunakan untuk mengembalikan barang ke gudang</p>
                                            <ol class="small">
                                                <li>Cari transaksi yang akan direfund</li>
                                                <li>Klik tombol <span class="badge bg-success">Refund</span></li>
                                                <li>Konfirmasi pengembalian</li>
                                                <li>Barang akan kembali ke stok gudang</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bagian Carousel Gambar -->
                            <div class="row justify-content-center">
                                <div class="col-12">
                                    <div id="carouselTransaksi" class="carousel slide text-center" 
                                        data-bs-ride="carousel" data-bs-interval="3000">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="{{ asset('assets/img/panduan_admin/transaksi/transaksiedit.png') }}" class="d-block mx-auto w-100 rounded shadow" alt="Edit Transaksi">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/transaksi/transaksieditmodal.png') }}" class="d-block mx-auto w-100 rounded shadow" alt="Modal Edit">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/transaksi/transaksieditcode.png') }}" class="d-block mx-auto w-100 rounded shadow" alt="Edit Code">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/transaksi/transaksieditsimpan.png') }}" class="d-block mx-auto w-100 rounded shadow" alt="Simpan Edit">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/transaksi/transaksieditvalidasi.png') }}" class="d-block mx-auto w-100 rounded shadow" alt="Validasi Edit">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/transaksi/transaksirefund.png') }}" class="d-block mx-auto w-100 rounded shadow" alt="Refund">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/transaksi/transaksirefundmodal.png') }}" class="d-block mx-auto w-100 rounded shadow" alt="Modal Refund">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/transaksi/transaksirefundvaldiasi.png') }}" class="d-block mx-auto w-100 rounded shadow" alt="Validasi Refund">
                                            </div>
                                        </div>

                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselTransaksi" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselTransaksi" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>

                                    <p class="text-muted small mt-2">Contoh tampilan halaman transaksi</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <!-- Tab 4: Barang Rusak -->
                <div class="tab-pane fade" id="rusak" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h3 class="card-title mb-0">
                                <i class="ri ri-close-circle-line text-primary me-2"></i>
                                Mencatat Barang Rusak
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center mb-4">
                                <div class="col-md-6 mb-4">
                                    <h4 class="text-primary">Langkah-langkah:</h4>
                                    <ol class="list-group list-group-numbered">
                                        <li class="list-group-item border-0 px-0">Buka halaman <strong>Barang Rusak / Reject</strong></li>
                                        <li class="list-group-item border-0 px-0">Pilih kategori kerusakan:
                                            <ul class="mt-2">
                                                <li><span class="badge bg-warning text-dark">Rusak Ringan</span> - Masih bisa digunakan dengan batasan</li>
                                                <li><span class="badge bg-danger">Rusak Berat</span> - Perlu perbaikan sebelum digunakan</li>
                                                <li><span class="badge bg-secondary">Tidak Bisa Digunakan</span> - Harus diganti/dibuang</li>
                                            </ul>
                                        </li>
                                        <li class="list-group-item border-0 px-0">Scan atau pilih barang yang rusak</li>
                                        <li class="list-group-item border-0 px-0">Tulis deskripsi kerusakan pada kolom yang tersedia</li>
                                        <li class="list-group-item border-0 px-0">Simpan data barang rusak</li>
                                        <li class="list-group-item border-0 px-0">Data akan tersimpan dan dapat dilihat di <strong>Data Barang Rusak / Reject</strong></li>
                                    </ol>
                                </div>
                                <div class="col-12 text-center">
                                    <div id="carouselRusak" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="{{ asset('assets/img/panduan_admin/rusak/rusak.png') }}" class="d-block w-100 rounded shadow" alt="Halaman Rusak">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/rusak/rusakdeskripsi.png') }}" class="d-block w-100 rounded shadow" alt="Deskripsi Rusak">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/rusak/rusakvalidasi.png') }}" class="d-block w-100 rounded shadow" alt="Validasi Rusak">
                                            </div>
                                        </div>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselRusak" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselRusak" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>
                                    <p class="text-muted small mt-2">Contoh pencatatan barang rusak</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 5: Export Data -->
                <div class="tab-pane fade" id="export" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h3 class="card-title mb-0">
                                <i class="ri ri-download-2-line text-primary me-2"></i>
                                Mengekspor Data Barang Keluar
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center mb-4">
                                <div class="col-md-6  mb-4">
                                    <h4 class="text-primary">Langkah-langkah:</h4>
                                    <ol class="list-group list-group-numbered">
                                        <li class="list-group-item border-0 px-0">Buka halaman <strong>Export Barang Keluar</strong></li>
                                        <li class="list-group-item border-0 px-0">Pilih tanggal mulai dan tanggal akhir periode yang diinginkan</li>
                                        <li class="list-group-item border-0 px-0">Klik tombol <span class="badge bg-primary">Tampilkan Data</span></li>
                                        <li class="list-group-item border-0 px-0">Sistem akan menampilkan data barang keluar dalam periode tersebut</li>
                                        <li class="list-group-item border-0 px-0">Pilih format ekspor:
                                            <div class="mt-2">
                                                <button class="btn btn-danger btn-sm me-2"><i class="ri-file-pdf-line me-1"></i> PDF</button>
                                                <button class="btn btn-success btn-sm"><i class="ri-file-excel-line me-1"></i> Excel</button>
                                            </div>
                                        </li>
                                        <li class="list-group-item border-0 px-0">File akan diunduh secara otomatis</li>
                                    </ol>
                                </div>
                                <div class="col-12 text-center">
                                    <div id="carouselExport" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="{{ asset('assets/img/panduan_admin/export/export.png') }}" class="d-block w-100 rounded shadow" alt="Halaman Export">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/export/exportkopsurat.png') }}" class="d-block w-100 rounded shadow" alt="Kop Surat">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/export/exportpdf.png') }}" class="d-block w-100 rounded shadow" alt="Export PDF">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/export/exportexcel.png') }}" class="d-block w-100 rounded shadow" alt="Export Excel">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/export/exportpdfview.png') }}" class="d-block w-100 rounded shadow" alt="Preview PDF">
                                            </div>
                                        </div>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExport" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExport" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>
                                    <p class="text-muted small mt-2">Contoh halaman export data</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 6: Manajemen Tamu -->
                <div class="tab-pane fade" id="guest" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h3 class="card-title mb-0">
                                <i class="ri ri-user-line text-primary me-2"></i>
                                Mengelola Data Tamu dan Pegawai
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <h4 class="text-primary">Manajemen Tamu & Pegawai</h4>
                                    <ol class="list-group list-group-numbered">
                                        <li class="list-group-item border-0 px-0">Buka halaman <strong>List Guest</strong></li>
                                        <li class="list-group-item border-0 px-0">Untuk menambah tamu baru, klik tombol <span class="badge bg-success">Tambah Tamu</span></li>
                                        <li class="list-group-item border-0 px-0">Isi data tamu pada form yang muncul</li>
                                        <li class="list-group-item border-0 px-0">Untuk mengedit, klik tombol <span class="badge bg-warning text-dark">Edit</span> pada tamu yang diinginkan</li>
                                        <li class="list-group-item border-0 px-0">Pilih produk untuk tamu dengan klik <span class="badge bg-primary">Pilih Produk</span></li>
                                        <li class="list-group-item border-0 px-0">Simpan perubahan</li>
                                    </ol>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <h4 class="text-primary">Manajemen Pegawai</h4>
                                    <ol class="list-group list-group-numbered">
                                        <li class="list-group-item border-0 px-0">Buka halaman <strong>List Pegawai</strong></li>
                                        <li class="list-group-item border-0 px-0">Anda dapat melihat daftar pegawai</li>
                                        <li class="list-group-item border-0 px-0">Pilih produk untuk pegawai dengan klik <span class="badge bg-primary">Pilih Produk</span></li>
                                        <li class="list-group-item border-0 px-0">Catatan: Tidak dapat menambah atau mengedit data pegawai</li>
                                    </ol>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12 text-center">
                                    <div id="carouselGuest" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/tamu.png') }}" class="d-block w-100 rounded shadow" alt="Halaman Tamu">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/tamutambah.png') }}" class="d-block w-100 rounded shadow" alt="Tambah Tamu">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/tamueditmodal.png') }}" class="d-block w-100 rounded shadow" alt="Edit Tamu">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/tamueditdeskripsi.png') }}" class="d-block w-100 rounded shadow" alt="Deskripsi Edit">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/tamupilihproduk.png') }}" class="d-block w-100 rounded shadow" alt="Pilih Produk">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/tamumodalbarang.png') }}" class="d-block w-100 rounded shadow" alt="Modal Barang">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/tamuvalidasitambahcartitem.png') }}" class="d-block w-100 rounded shadow" alt="Validasi Cart">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/tamucart.png') }}" class="d-block w-100 rounded shadow" alt="Cart Tamu">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/tamucartmodal.png') }}" class="d-block w-100 rounded shadow" alt="Modal Cart">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/tamuitemoutguest.png') }}" class="d-block w-100 rounded shadow" alt="Item Out Tamu">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/pegawai.png') }}" class="d-block w-100 rounded shadow" alt="Halaman Pegawai">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/pegawaiproduk.png') }}" class="d-block w-100 rounded shadow" alt="Produk Pegawai">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/pegawaicart.png') }}" class="d-block w-100 rounded shadow" alt="Cart Pegawai">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="{{ asset('assets/img/panduan_admin/manage/pegawaicartmodal.png') }}" class="d-block w-100 rounded shadow" alt="Modal Cart Pegawai">
                                            </div>
                                        </div>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselGuest" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselGuest" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>
                                    <p class="text-muted small mt-2">Contoh halaman manajemen tamu & pegawai</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    /* Override semua komponen Primary menjadi warna orange */
    :root {
        --bs-primary: #FF9800;
        --bs-primary-rgb: 255, 152, 0;
    }

    /* Header lebih lembut */
    .bg-primary {
        background-color: #ffa726 !important;
    }

    /* Teks lebih besar agar mudah dibaca */
    body, p, li, .card-body, .list-group-item {
        font-size: 17px;
        line-height: 1.6;
    }

    h3.card-title, h4, h5 {
        font-size: 20px;
        font-weight: 600;
    }

    .nav-tabs .nav-link {
        font-size: 18px;
        padding: 14px 10px;
    }

    /* Tab aktif dibuat lebih jelas */
    .nav-tabs .nav-link.active {
        background-color: #FFF3E0 !important;
        border-color: #FF9800 !important;
        font-weight: 700;
    }

    /* Elemen interaktif diperbesar */
    button, .btn, .badge {
        font-size: 16px !important;
    }

    /* Carousel Gambar Standar */
    .carousel-item img {
        width: 100%;
        max-height: 360px;
        object-fit: contain;
        background: #FFF7ED;
        border-radius: 12px;
        padding: 8px;
    }

    /* Kontrol navigasi carousel lebih besar */
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        width: 45px;
        height: 45px;
        background-color: rgba(0,0,0,0.35);
        border-radius: 50%;
    }

    /* Numbered step lebih besar dan jelas */
    .list-group-numbered > .list-group-item::before {
        color: white;
        font-weight: bold;
    }

    /* Tips box dibuat lebih friendly */
    .alert-info {
        background-color: #FFF3E0 !important;
        color: #6A4E23;
        border-left: 5px solid #FF9800;
    }

    /* Spasi lebih lega antar section */
    .row.mb-4, .alert {
        margin-bottom: 32px !important;
    }

</style>

<script>
    // Initialize carousels with proper configuration
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all carousels
        const carousels = document.querySelectorAll('.carousel');
        carousels.forEach(carousel => {
            // Set interval for automatic sliding
            const carouselInstance = new bootstrap.Carousel(carousel, {
                interval: 3000,
                wrap: true,
                pause: 'hover'
            });
        });

        // Tab activation
        const triggerTabList = [].slice.call(document.querySelectorAll('#panduanTabs button'))
        triggerTabList.forEach(function (triggerEl) {
            const tabTrigger = new bootstrap.Tab(triggerEl)

            triggerEl.addEventListener('click', function (event) {
                event.preventDefault()
                tabTrigger.show()
            })
        });
    });
</script>
@endsection