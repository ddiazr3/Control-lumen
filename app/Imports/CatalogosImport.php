<?php

namespace App\Imports;

use App\Models\Categoria;
use App\Models\Linea;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class CatalogosImport implements ToModel
{
    /**
    * @param Collection $collection
    */


    protected $model;
    protected $empresaid;
    //

    public function __construct($model = null, $empresaid = null)
    {
        $this->model = $model;
        $this->empresaid = $empresaid;
    }

    public function model(array $row)
    {
        switch ($this->model){
            case 'marca' : return self::marca($row,$this->empresaid);
                            break;
            case 'linea' : return self::linea($row,$this->empresaid);
                break;
            case 'proveedor' : return self::proveedor($row,$this->empresaid);
                break;
            case 'categoria' : return self::categoria($row,$this->empresaid);
                break;
        }
    }

    private static function marca($row, $empresaid) {
        return new Marca([
            'nombre' => $row[0],
            "empresaid" => $empresaid
        ]);
    }

    private static function linea($row, $empresaid) {
        return new Linea([
            'nombre' => $row[0],
            "empresaid" => $empresaid,
            "marcaid" => $row[1]
        ]);
    }

    private static function proveedor($row, $empresaid) {
        return new Proveedor([
            'nombre' => $row[0],
            'direccion' => $row[1],
            'telefono' => $row[2],
            "empresaid" => $empresaid
        ]);
    }

    private static function categoria($row, $empresaid) {
        return new Categoria([
            'nombre' => $row[0],
            "empresaid" => $empresaid
        ]);
    }
}
