<?php

namespace App\Http\Controllers\Role\Admin;

use App\Models\Item_out;
use App\Models\Guest_carts_item;
use App\Models\KopSurat;
use App\Models\ExportLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BarangKeluarExportAdmin implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $start;
    protected $end;
    protected $kopSuratId;
    protected $totalItems = 0;
    protected $totalQuantity = 0;

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
            ->whereNotNull('released_at')
            ->whereBetween(DB::raw('DATE(released_at)'), [$this->start, $this->end])
            ->get()
            ->map(function ($row) {
                $row->type = 'tamu';
                $row->pengambil = $row->guestCart->guest->name ?? 'Tamu';
                $row->released_date = Carbon::parse($row->released_at)->format('d-m-Y');
                return $row;
            });

        // Gabungkan data dan urutkan
        $collection = $pegawaiItems->concat($guestItems)
            ->sortByDesc(function ($item) {
                return $item->released_at ?? $item->created_at;
            })
            ->values();

        // Hitung total
        $this->totalItems = $collection->count();
        $this->totalQuantity = $collection->sum('quantity');

        return $collection;
    }

    /**
     * Menentukan header kolom di file Excel.
     * SESUAI URUTAN TABLE DI BLADE
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'NO',
            'NAMA BARANG',
            'PENERIMA',
            'ROLE',
            'TANGGAL KELUAR',
            'JUMLAH'
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

        // Tentukan nama barang
        $namaBarang = $item->item->name ?? 'Barang Dihapus';

        // Tentukan penerima/pengambil
        $penerima = $item->pengambil ?? (
            $item->type === 'pegawai'
            ? ($item->cart->user->name ?? 'Tamu/Non-User')
            : ($item->guestCart->guest->name ?? 'Tamu')
        );

        // Tentukan role
        $role = $item->type === 'pegawai' ? 'Pegawai' : 'Tamu';

        // Format tanggal
        $tanggal = $item->released_date ?? Carbon::parse($item->released_at ?? $item->created_at)->format('d-m-Y');

        // Format jumlah dengan satuan jika ada
        $jumlah = $item->quantity;

        return [
            $counter,
            $namaBarang,
            $penerima,
            $role,
            $tanggal,
            $jumlah
        ];
    }

    /**
     * Events untuk styling dan menambahkan total
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Dapatkan total row (data + header)
                $totalRows = $this->totalItems + 1; // +1 untuk header

                // Tambahkan row untuk TOTAL JUMLAH BARANG
                $event->sheet->append([
                    ['', '', '', '', 'TOTAL JUMLAH BARANG', $this->totalItems]
                ]);

                // Styling untuk header
                $event->sheet->getStyle('A1:F1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '6c757d'] // text-secondary
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => 'FFF3E0'] // background header
                    ]
                ]);

                // Styling untuk total row
                $totalRow = $totalRows + 1; // Row setelah data terakhir
                $event->sheet->getStyle("A{$totalRow}:F{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => 'f8f9fa'] // background abu-abu muda
                    ]
                ]);

                // Center align untuk semua cell
                $event->sheet->getStyle('A1:F' . $totalRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Auto size kolom
                foreach (range('A', 'F') as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Add border untuk semua cell
                $event->sheet->getStyle('A1:F' . $totalRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }

    /**
     * Method untuk mendapatkan total quantity
     */
    public function getTotalQuantity()
    {
        return $this->totalQuantity;
    }

    /**
     * Method untuk mendapatkan total items
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * Method untuk mendapatkan data dengan format yang sama seperti PDF
     */
    public function getDataForPdf()
    {
        $data = $this->collection();

        return [
            'items' => $data,
            'totalQuantity' => $this->totalQuantity,
            'totalItems' => $this->totalItems,
            'period' => "Periode: " . date('d/m/Y', strtotime($this->start)) . " - " . date('d/m/Y', strtotime($this->end)),
            'kopSurat' => $this->kopSuratId ? KopSurat::find($this->kopSuratId) : null,
            'start' => $this->start,
            'end' => $this->end,
        ];
    }

    /**
     * Method untuk log export
     */
    public function logExport($format = 'excel')
    {
        $period = "Periode: " . date('d/m/Y', strtotime($this->start)) . " - " . date('d/m/Y', strtotime($this->end));

        $extension = ($format === 'excel') ? 'xlsx' : $format;
        $fileName = "barang_keluar_{$this->start}_to_{$this->end}_" . date('Ymd_His') . ".{$extension}";

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'data_type' => 'keluar',
            'format' => $format,
            'file_path' => "exports/{$fileName}",
            'period' => $period,
        ]);

        return $fileName;
    }
}