<?php

namespace App\Exports;

use App\Models\PurchaseReturn;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class Purchase_Return implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    function array(): array
    {
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        // Check If User Has Permission View  All Records
        $PurchaseReturn = PurchaseReturn::with('provider')
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })->orderBy('id', 'DESC')->get();

        if ($PurchaseReturn->isNotEmpty()) {
            foreach ($PurchaseReturn as $purchase_return) {

                $item['Ref'] = $purchase_return->Ref;
                $item['provider'] = $purchase_return['provider']->name;
                $item['statut'] = $purchase_return->statut;
                $item['GrandTotal'] = number_format($purchase_return->GrandTotal, 2);
                $item['Paid'] = number_format($purchase_return->paid_amount, 2);
                $item['due'] = number_format($purchase_return->GrandTotal - $purchase_return->paid_amount, 2);
                $item['payment_status'] = $purchase_return->payment_statut;

                $data[] = $item;
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
                $cellRange = 'A1:G1'; // All headers
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
            'Reference',
            'Supplier',
            'Status',
            'Total',
            'Paid',
            'Due',
            'Payment Status',
        ];
    }
}
