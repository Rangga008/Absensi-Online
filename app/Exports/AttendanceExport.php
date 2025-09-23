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
            'Checkout Status',
            'Checkout Time',
            'Durasi Kerja',
            'Latitude',
            'Longitude',
            'Jarak (m)',
            'Checkout Latitude',
            'Checkout Longitude',
            'Checkout Jarak (m)',
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
            $attendance->hasCheckedOut() ? 'Sudah Keluar' : 'Belum Keluar',
            $attendance->checkout_at ? $attendance->checkout_at->format('H:i:s') : '',
            $attendance->work_duration_formatted ?: '-',
            $attendance->latitude,
            $attendance->longitude,
            $attendance->distance,
            $attendance->checkout_latitude,
            $attendance->checkout_longitude,
            $attendance->checkout_distance,
            $attendance->ip_address
        ];
    }
}
