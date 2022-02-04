<?php

namespace App\Exports;

use App\Models\Role;
use App\Models\Transfer;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class TransfersExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    function array(): array
    {

        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        // Check If User Has Permission View  All Records
        $Transfers = Transfer::with('From_warehouse', 'To_warehouse')
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })->orderBy('id', 'DESC')->get();

        if ($Transfers->isNotEmpty()) {
            foreach ($Transfers as $transfer) {

                $trans['date'] = $transfer->date;
                $trans['Ref'] = $transfer->Ref;
                $trans['from_warehouse'] = $transfer['From_warehouse']->name;
                $trans['to_warehouse'] = $transfer['To_warehouse']->name;
                $trans['items'] = $transfer->items . ' ' . 'Products';
                $trans['statut'] = $transfer->statut;

                $data[] = $trans;
            }
        } else {
            $data = [];
        }
        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:F1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);

                $styleArray = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],

                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                ];

            },
        ];

    }

    public function headings(): array
    {
        return [
            'Date',
            'Réference',
            'From Warehouse',
            'To Warehouse',
            'Items',
            'Status',
        ];
    }
}
