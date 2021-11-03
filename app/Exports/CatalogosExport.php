<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class CatalogosExport implements FromCollection, WithHeadings
{
    use Exportable;

    protected $data;
    protected $header;

    //

    public function __construct($data = null,$header = null)
    {
        $this->data = $data;
        $this->header = $header;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array{
        return $this->header;
    }
}
