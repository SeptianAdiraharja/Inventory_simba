<?php

namespace App\Http\Controllers\Role\Admin;

use App\Models\Item_out;
use App\Models\Guest_carts_item;
use App\Models\KopSurat;
use App\Models\ExportLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BarangKeluarExportAdmin implements FromCollection, WithHeadings, WithMapping
{
    protected $start;
    protected $end;
    protected $kopSuratId;

    public function __construct($start, $end, $kopSuratId = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->kopSuratId = $kopSuratId;
    }

    /**
     * Mengambil data barang keluar untuk diexport ke Excel.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Data dari Item_out (pegawai)
        $pegawaiItems = Item_out::with(['item', 'cart.user', 'approver'])
            ->where(function ($q) {
                $q->whereBetween(DB::raw('DATE(released_at)'), [$this->start, $this->end])
                ->orWhereBetween(DB::raw('DATE(created_at)'), [$this->start, $this->end]);
            })
            ->get()
            ->map(function ($row) {
                $row->type = 'pegawai';
                $row->pengambil = $row->cart->user->name ?? 'Tamu/Non-User';
                $row->released_date = Carbon::parse($row->released_at ?? $row->created_at)->format('d-m-Y');
                return $row;
            });

        // Data dari Guest_carts_item (tamu)
        $guestItems = Guest_carts_item::with(['item', 'guestCart.guest'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$this->start, $this->end])
            ->get()
            ->map(function ($row) {
                $row->type = 'tamu';
                $row->pengambil = $row->guestCart->guest->name ?? 'Tamu';
                $row->released_at = $row->created_at;
                $row->released_date = Carbon::parse($row->created_at)->format('d-m-Y');
                return $row;
            });

        // Gabungkan data dan urutkan
        return $pegawaiItems->concat($guestItems)
            ->sortByDesc(function ($item) {
                return $item->released_at ?? $item->created_at;
            })
            ->values();
    }

    /**
     * Menentukan header kolom di file Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'NO',
            'NAMA BARANG',
            'JUMLAH',
            'TANGGAL KELUAR',
            'PENGAMBIL',
            'ROLE'
        ];
    }

    /**
     * Mapping data untuk Excel.
     *
     * @param mixed $item
     * @return array
     */
    public function map($item): array
    {
        static $counter = 0;
        $counter++;

        // Tentukan nama barang dan pengambil berdasarkan jenis
        $namaBarang = $item->item->name ?? 'Barang Dihapus';

        $pengambil = $item->pengambil ?? (
            $item->type === 'pegawai'
            ? ($item->cart->user->name ?? 'Tamu/Non-User')
            : ($item->guestCart->guest->name ?? 'Tamu')
        );

        $jenis = $item->type === 'pegawai' ? 'Pegawai' : 'Tamu';

        return [
            $counter,
            $namaBarang,
            $item->quantity,
            $item->released_date ?? Carbon::parse($item->released_at ?? $item->created_at)->format('d-m-Y'),
            $pengambil,
            $jenis
        ];
    }

    /**
     * Method untuk mendapatkan total quantity (jika diperlukan)
     */
    public function getTotalQuantity()
    {
        return $this->collection()->sum('quantity');
    }

    /**
     * Method untuk mendapatkan data dengan format yang sama seperti PDF
     */
    public function getDataForPdf()
    {
        $data = $this->collection();

        // Format data untuk PDF
        $totalQuantity = 0;
        $data->map(function ($row) use (&$totalQuantity) {
            $row->released_date = Carbon::parse($row->released_at ?? $row->created_at)->format('d-m-Y');
            $totalQuantity += $row->quantity;
            return $row;
        });

        return [
            'items' => $data,
            'totalQuantity' => $totalQuantity,
            'period' => "Periode: " . date('d/m/Y', strtotime($this->start)) . " - " . date('d/m/Y', strtotime($this->end)),
            'kopSurat' => $this->kopSuratId ? KopSurat::find($this->kopSuratId) : null,
            'start' => $this->start,
            'end' => $this->end,
        ];
    }

    /**
     * Method untuk log export (jika diperlukan di Excel)
     */
    /**
 * Method untuk log export (jika diperlukan di Excel)
 */
    public function logExport($format = 'excel')
    {
        $period = "Periode: " . date('d/m/Y', strtotime($this->start)) . " - " . date('d/m/Y', strtotime($this->end));

        // Pastikan ekstensi file sesuai dengan format
        $extension = ($format === 'excel') ? 'xlsx' : $format;
        $fileName = "barang_keluar_{$this->start}_to_{$this->end}_" . date('Ymd_His') . ".{$extension}";

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'data_type'      => 'keluar',
            'format'         => $format,
            'file_path'      => "exports/{$fileName}",
            'period'         => $period,
        ]);

        return $fileName;
    }
}