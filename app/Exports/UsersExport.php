<?php

namespace App\Exports;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class UsersExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    function array(): array
    {
        $Role = Auth::user()->roles()->first();
        $ShowRecord = Role::findOrFail($Role->id)->inRole('record_view');

        $data = [];
        $users = User::where('deleted_at', '=', null)
            ->where(function ($query) use ($ShowRecord) {
                if ($ShowRecord !== 1) {
                    return $query->where('id', '=', Auth::user()->id);
                }})->get();

        foreach ($users as $user) {

            $item['firstname'] = $user->firstname;
            $item['lastname'] = $user->lastname;
            $item['username'] = $user->username;
            $item['email'] = $user->email;
            $item['phone'] = $user->phone;

            if ($user->statut === 1) {
                $item['statut'] = 'Actif';
            } else {
                $item['statut'] = 'Inactif';
            }

            $data[] = $item;
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
            'First Name',
            'Last Name',
            'Username',
            'Email',
            'Phone',
            'Status',
        ];
    }
}
