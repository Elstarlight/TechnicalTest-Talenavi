<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TodosExport implements FromCollection, WithHeadings, WithMapping, WithEvents, ShouldAutoSize, WithStyles
{
    protected Collection $todos;

    public function __construct(Collection $todos)
    {
        $this->todos = $todos;
    }

    public function collection()
    {
        return $this->todos;
    }

    public function headings(): array
    {
        return ['Title', 'Assignee', 'Due Date', 'Time Tracked', 'Status', 'Priority'];
    }

    public function map($todo): array
    {
        return [
            $todo->title,
            $todo->assignee,
            optional($todo->due_date)->format('Y-m-d'),
            (float) $todo->time_tracked,
            $todo->status,
            $todo->priority,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = $this->todos->count() + 1;
                $summaryRow = $lastDataRow + 2;

                $totalTodos = $this->todos->count();
                $totalTimeTracked = $this->todos->sum('time_tracked');

                $sheet->setCellValue('A' . $summaryRow, 'Total Todos');
                $sheet->setCellValue('B' . $summaryRow, $totalTodos);

                $sheet->setCellValue('A' . ($summaryRow + 1), 'Total Time Tracked');
                $sheet->setCellValue('B' . ($summaryRow + 1), $totalTimeTracked);

                $sheet->getStyle('A' . $summaryRow . ':A' . ($summaryRow + 1))
                    ->getFont()->setBold(true);
            },
        ];
    }
}
