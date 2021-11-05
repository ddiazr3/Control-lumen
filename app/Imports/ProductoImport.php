<?php

namespace App\Imports;

use App\Models\Bodega;
use App\Models\Categoria;
use App\Models\Linea;
use App\Models\Marca;
use App\Models\PrecioBodega;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\StockBodega;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductoImport implements ToCollection, WithValidation
{
    use Importable;

    protected $empresaid;
    protected $marca = null;
    //

    public function __construct($empresaid = null)
    {
        $this->empresaid = $empresaid;
    }

    public function collection(Collection $collection)
    {
        foreach ($collection as $row){
            $prod = new Producto();
            $prod->nombre = $row[0];
            $prod->descripcion = $row[1];
            $prod->codigo = $row[2];
            $prod->proveedorid = $row[7] ?? null;
            $prod->marcaid = $row[5] ?? null;
            $prod->lineaid = $row[6] ?? null;
            $prod->categoriaid = $row[8] ?? null;
            $prod->empresaid =  $this->empresaid;
            $prod->save();

            $bodega = Bodega::where('empresaid',$this->empresaid)->first();

            $catidadB = new StockBodega();
            $catidadB->cantidad = $row[3];
            $catidadB->productoid = $prod->id;
            $catidadB->bodegaid = $bodega->id;
            $catidadB->save();

            $precioB = new precioBodega();
            $precioB->precio = $row[4];
            $precioB->productoid = $prod->id;
            $precioB->bodegaid = $bodega->id;
            $precioB->save();
        }
    }

    public function rules(): array
    {
        return [
            // Can also use callback validation rules
            '5' => function($attribute, $value, $onFailure) {
                $porciones = explode(".", $attribute);
                $this->marca = Marca::find($value);
                if (!$this->marca) {
                    $onFailure("Marca no exite en la fila $porciones[0] columna $porciones[1] con el valor de $value");
                }
            },
            '6' => function($attribute, $value, $onFailure) {
                $porciones = explode(".", $attribute);
                $linea = Linea::find($value);
                if (!$linea) {
                    $onFailure("Linea no exite en la fila $porciones[0] columna $porciones[1] con el valor de $value");
                }
            },
            '7' => function($attribute, $value, $onFailure) {
                $porciones = explode(".", $attribute);
                $prove = Proveedor::find($value);
                if (!$prove) {
                    $onFailure("Proveedor no exite en la fila $porciones[0] columna $porciones[1] con el valor de $value");
                }
            },
            '8' => function($attribute, $value, $onFailure) {
                $porciones = explode(".", $attribute);
                $categ = Categoria::find($value);
                if (!$categ) {
                    $onFailure("Categoria no exite en la fila $porciones[0] columna $porciones[1] con el valor de $value");
                }
            },
        ];
    }
}
