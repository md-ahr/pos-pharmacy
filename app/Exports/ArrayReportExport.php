<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ArrayReportExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @param  list<string>  $headings
     * @param  Collection<int, array<int, string|int|float|null>>|list<array<int, string|int|float|null>>  $rows
     */
    public function __construct(
        private string $title,
        private array $headings,
        private Collection|array $rows,
    ) {}

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }
}
