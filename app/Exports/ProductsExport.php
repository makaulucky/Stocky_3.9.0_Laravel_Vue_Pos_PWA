<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\product_warehouse;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductsExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    function array(): array
    {
        $products = Product::where('deleted_at', '=', null)
            ->orderBy('id', 'DESC')
            ->get();

        if ($products->isNotEmpty()) {

            foreach ($products as $product) {
                $product_warehouse_data = product_warehouse::where('deleted_at', '=', null)
                    ->where('product_id', $product->id)->get();

                $qte = 0;
                $item['code'] = $product->code;
                $item['name'] = $product->name;
                $item['category'] = $product['category']->name;
                $item['brand'] = $product['brand'] ? $product['brand']->name : 'N/D';
                foreach ($product_warehouse_data as $product_warehouse) {
                    $qte += $product_warehouse->qte;
                    $item['quantity'] = $qte;
                }
                $item['unit'] = $product['unit']->ShortName;
                $item['cost'] = $product->cost;
                $item['price'] = $product->price;

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
                $cellRange = 'A1:H1'; // All headers
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
            'Code',
            'Name',
            'Category',
            'Brand',
            'Quantity',
            'Unit',
            'Cost',
            'Price',
        ];
    }
}
