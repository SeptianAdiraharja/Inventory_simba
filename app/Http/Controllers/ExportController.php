<?php

namespace App\Http\Controllers;

use App\Models\Item_in;
use App\Models\Item_out;
use App\Models\ExportLog;
use App\Models\Reject;
use App\Models\Guest_carts_item;
use App\Models\KopSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BarangMasukExport;
use App\Exports\BarangKeluarExport;
use App\Http\Controllers\Role\admin\BarangKeluarExportAdmin;
use Illuminate\Support\Facades\Log;

class ExportController extends Controller
{
    /** 🔹 Hitung endDate berdasarkan startDate + periode */
    private function calculateEndDate($startDate, $period)
    {
        if (!$startDate) return null;

        $start = \Carbon\Carbon::parse($startDate);

        return match ($period) {
            'weekly'  => $start->copy()->addWeek()->format('Y-m-d'),
            'monthly' => $start->copy()->addMonth()->format('Y-m-d'),
            'yearly'  => $start->copy()->addYear()->format('Y-m-d'),
            default   => $start->copy()->format('Y-m-d'),
        };
    }

    /** 🔹 Filter berdasarkan rentang tanggal */

    private function filterByDateRange($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59'
            ]);
        }
        return $query;
    }

    /** 🔹 Halaman utama export */
    public function index(Request $request)
    {
        $search = $request->get('q');
        $items = collect();
        $logs  = ExportLog::orderBy('created_at', 'desc')->get();

        $startDate = $request->query('start_date');
        $period    = $request->query('period', 'weekly');
        $type      = $request->query('type', 'masuk');
        $format    = $request->query('format', 'excel');

        $endDate = $this->calculateEndDate($startDate, $period);

        if ($startDate && $endDate) {
            // 🔹 Barang Masuk
            if ($type === 'masuk') {
                $items = Item_in::with('item.unit', 'supplier')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc') // ✅ Data terbaru di atas
                    ->get()
                    ->map(function ($row) {
                        $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                        return $row;
                    });
            }

            // 🔹 Barang Keluar - FIXED
            elseif ($type === 'keluar') {
                $pegawaiItems = Item_out::with(['item.unit', 'cart.user'])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc') // ✅ Data terbaru di atas
                    ->get()
                    ->map(function ($row) {
                        $row->role        = 'Pegawai';
                        $row->dikeluarkan = 'Petugas Gudang';
                        $row->penerima    = $row->cart->user->name ?? '-';
                        $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                        return $row;
                    });

                $guestItems = Guest_carts_item::with([
                    'item.unit',
                    'guestCart.guest'
                ])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc') // ✅ Data terbaru di atas
                ->get()
                ->map(function ($row) {
                    $row->role        = 'Tamu';
                    $row->dikeluarkan = 'Petugas Gudang';

                    if ($row->guestCart && $row->guestCart->guest) {
                        $row->penerima = $row->guestCart->guest->name;
                    } else {
                        $row->penerima = $row->guest_name ??
                                        $row->guestCart->guest_name ??
                                        'Tamu';
                    }

                    $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                    return $row;
                });

                // Gabungkan dan urutkan DESC berdasarkan created_at
                $items = $pegawaiItems->concat($guestItems)
                    ->sortByDesc('created_at') // ✅ Data terbaru di atas
                    ->values();
            }

            // 🔹 Barang Reject
            elseif ($type === 'reject') {
                $items = Reject::with('item.unit')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc') // ✅ Data terbaru di atas
                    ->get()
                    ->map(function ($row) {
                        $row->role = $row->condition ?? '-';
                        $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                        return $row;
                    });
            }

            // 🔹 Semua Data
            elseif ($type === 'all') {
                $barangMasuk = Item_in::with('item.unit', 'supplier')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc') // ✅ Data terbaru di atas
                    ->get()
                    ->map(function ($row) {
                        $row->role        = 'Supplier';
                        $row->dikeluarkan = $row->supplier->name ?? '-';
                        $row->penerima    = '-';
                        $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                        return $row;
                    });

                $pegawaiItems = Item_out::with(['item.unit', 'approver'])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc') // ✅ Data terbaru di atas
                    ->get()
                    ->map(function ($row) {
                        $row->role        = 'Pegawai';
                        $row->dikeluarkan = $row->approver->name ?? 'Petugas Gudang';
                        $row->penerima    = $row->approver->name ?? '-';
                        $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                        return $row;
                    });

                $guestItems = Guest_carts_item::with(['item.unit', 'guestCart.guest'])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc') // ✅ Data terbaru di atas
                    ->get()
                    ->map(function ($row) {
                        $row->role        = 'Guest';
                        $row->dikeluarkan = 'Petugas Gudang';
                        $row->penerima    = $row->guestCart->guest->name ?? '-';
                        $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                        return $row;
                    });

                // Gabungkan dan urutkan DESC berdasarkan created_at
                $items = $barangMasuk->concat($pegawaiItems)->concat($guestItems)
                    ->sortByDesc('created_at') // ✅ Data terbaru di atas
                    ->values();
            }
        }

        $kopSurat = KopSurat::all();
        return view('role.super_admin.exports.index', compact(
            'items', 'logs', 'period', 'startDate', 'endDate', 'format', 'type', 'kopSurat'
        ));
    }


    /** 🔹 Download data */
    public function download(Request $request)
    {
        // 🔹 Cek pilihan kop surat
        if (!$request->has('kop_surat')) {
            return back()->with('warning', 'Silakan pilih kop surat terlebih dahulu sebelum mengunduh.');
        }

        $kopSuratId = $request->input('kop_surat');
        $kopSurat   = KopSurat::find($kopSuratId);

        if (!$kopSurat) {
            return back()->with('warning', 'Kop surat yang dipilih tidak ditemukan.');
        }

        $startDate = $request->query('start_date');
        $period    = $request->query('period', 'weekly');
        $type      = $request->query('type', 'masuk');
        $format    = $request->query('format', 'excel');
        $endDate   = $this->calculateEndDate($startDate, $period);
        $periodeText = "{$startDate} s/d {$endDate}";

        $controllerData = $this->index($request)->getData();
        $items = $controllerData['items'] ?? collect();

        $totalJumlah = $items->sum('quantity');
        $grandTotal  = $items->sum('total_price');
        $fileName    = "barang_{$type}{$startDate}_to{$endDate}_" . now()->format('Ymd_His');

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type'           => $period,
            'data_type'      => $type,
            'format'         => $format,
            'file_path'      => "role/super_admin/exports/{$fileName}.{$format}",
            'period'         => $periodeText,
        ]);

        if ($format === 'excel') {
            return $type === 'masuk'
                ? Excel::download(new BarangMasukExport($items, $totalJumlah, $grandTotal), "{$fileName}.xlsx")
                : Excel::download(new BarangKeluarExport($items, $totalJumlah, $grandTotal), "{$fileName}.xlsx");
        }

        $options = [
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'isRemoteEnabled' => true
        ];

        if ($type === 'masuk') {
            $pdf = Pdf::loadView('role.super_admin.exports.barang_masuk_pdf', compact(
                'items','startDate','endDate','periodeText','totalJumlah','grandTotal','kopSurat'
            ))->setOptions($options);
        } elseif ($type === 'keluar') {
            $pdf = Pdf::loadView('role.super_admin.exports.barang_keluar_pdf', compact(
                'items','startDate','endDate','periodeText','totalJumlah','grandTotal','kopSurat'
            ))->setOptions($options);
        } else {
            $pdf = Pdf::loadView('role.super_admin.exports.barang_reject_pdf', compact(
                'items','startDate','endDate','periodeText','totalJumlah','kopSurat'
            ))->setOptions($options);
        }


        return $pdf->setPaper('a4', 'landscape')->download("{$fileName}.pdf");

    }

    /** 🔹 Bersihkan log */
    public function clearLogs()
    {
        ExportLog::truncate();
        return redirect()->route('super_admin.export.index')
            ->with('success', 'Riwayat export berhasil dibersihkan.');
    }

    // ===============================================================
    // 🔹 BAGIAN ADMIN (Tetap dipertahankan)
    // ===============================================================

    public function exportOut(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $format    = $request->input('format', 'pdf');

        // PERBAIKAN: Hapus kolom 'period' dari select
        $exports = ExportLog::where('data_type', 'keluar')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'format', 'file_path', 'created_at']); // Hapus 'period'

        $items = collect();

        if ($startDate && $endDate) {
            // Data dari Item_out (pegawai)
            $pegawaiItems = Item_out::with('item', 'cart.user', 'approver')
                ->whereBetween('created_at', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ])
                ->orderByRaw('GREATEST(
                    UNIX_TIMESTAMP(released_at),
                    UNIX_TIMESTAMP(created_at)
                ) DESC')
                ->get()
                ->map(function ($row) {
                    $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                    $row->type = 'pegawai';
                    $row->pengambil = $row->cart->user->name ?? 'Tamu/Non-User';
                    return $row;
                });

            // Data dari Guest_carts_item (tamu)
            $guestItems = Guest_carts_item::with('item', 'guestCart.guest')
                ->whereBetween('created_at', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ])
                ->orderBy('created_at', 'DESC')
                ->get()
                ->map(function ($row) {
                    $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                    $row->type = 'tamu';
                    $row->pengambil = $row->guestCart->guest->name ?? 'Tamu';
                    $row->released_at = $row->created_at;
                    return $row;
                });

            // Gabungkan dan urutkan berdasarkan tanggal
            $items = $pegawaiItems->concat($guestItems)
                ->sortByDesc(function ($item) {
                    return $item->released_at ?? $item->created_at;
                })
                ->values();
        }

        $kopSurat = KopSurat::all();

        return view('role.admin.barangkeluar', [
            'exports'   => $exports,
            'items'     => $items,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'format'    => $format,
            'kopSurat'  => $kopSurat,
        ]);
    }

    public function clearOutHistory()
    {
        DB::table('export_logs')->where('data_type', 'keluar')->delete();
        return redirect()->back()->with('success', 'Riwayat export barang keluar berhasil dibersihkan.');
    }

    public function exportBarangKeluarExcelAdmin(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $kopSuratId = $request->query('kop_surat');

        // Debug: cek parameter
        Log::info('Excel Export Parameters:', [
            'start_date' => $start,
            'end_date' => $end,
            'kop_surat' => $kopSuratId
        ]);

        // Validasi kop surat
        if (!$kopSuratId) {
            Log::warning('Kop surat tidak dipilih untuk export Excel');
            return back()->with('warning', 'Silakan pilih kop surat terlebih dahulu sebelum mengunduh.');
        }

        try {
            $export = new BarangKeluarExportAdmin($start, $end, $kopSuratId);

            // Log export dan dapatkan nama file dengan ekstensi .xlsx
            $fileName = $export->logExport('excel');

            Log::info('Excel Export Success:', ['file_name' => $fileName]);

            // Pastikan menggunakan \Maatwebsite\Excel\Facades\Excel
            return \Maatwebsite\Excel\Facades\Excel::download($export, $fileName, \Maatwebsite\Excel\Excel::XLSX);

        } catch (\Exception $e) {
            Log::error('Excel Export Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat export Excel: ' . $e->getMessage());
        }
    }

    public function exportBarangKeluarPdfAdmin(Request $request)
    {
        // 🔹 Cek pilihan kop surat
        if (!$request->has('kop_surat') || empty($request->input('kop_surat'))) {
            return back()->with('warning', 'Silakan pilih kop surat terlebih dahulu sebelum mengunduh.');
        }

        $kopSuratId = $request->input('kop_surat');
        $kopSurat = KopSurat::find($kopSuratId);

        if (!$kopSurat) {
            return back()->with('warning', 'Kop surat yang dipilih tidak ditemukan.');
        }

        $start = date('Y-m-d', strtotime($request->query('start_date')));
        $end = date('Y-m-d', strtotime($request->query('end_date')));

        // Gunakan class export untuk mendapatkan data yang konsisten
        $export = new BarangKeluarExportAdmin($start, $end, $kopSuratId);
        $data = $export->getDataForPdf();

        if ($data['items']->isEmpty()) {
            return back()->with('warning', 'Tidak ada data barang keluar pada periode ini.');
        }

        // 🔹 KONFIGURASI DOMpdf YANG BENAR - GUNAKAN ARRAY
        $options = [
            'isPhpEnabled' => true, // 🔹 YANG INI PALING PENTING
            'isRemoteEnabled' => true,
            'defaultFont' => 'Helvetica',
            'chroot' => public_path(),
        ];

        $pdf = Pdf::loadView('role.admin.export.barang_keluar_pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions($options); // 🔹 SET OPTIONS SEBAGAI ARRAY

        // Log export
        $fileName = $export->logExport('pdf');

        return $pdf->download($fileName);
    }
}
