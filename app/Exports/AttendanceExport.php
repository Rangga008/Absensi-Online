<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    private $attendances;

    public function __construct($attendances)
    {
        $this->attendances = $attendances;
    }

    public function collection()
    {
        return $this->attendances;
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Role',
            'Tanggal',
            'Waktu',
            'Status',
            'Latitude',
            'Longitude',
            'Jarak (m)',
            'IP Address'
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->user->name ?? '',
            $attendance->user->role->role_name ?? '',
            $attendance->present_date,
            $attendance->present_at ? $attendance->present_at->format('H:i:s') : '',
            $attendance->description,
            $attendance->latitude,
            $attendance->longitude,
            $attendance->distance,
            $attendance->ip_address
        ];
    }
}
