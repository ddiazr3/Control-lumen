<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => 'auth','prefix' => 'api'], function () use ($router) {
    $router->get('roles','Configuracion\RoleController@index');
    $router->post('roles','Configuracion\RoleController@store');
    $router->get('roles/{id}/edit','Configuracion\RoleController@edit');
    $router->post('roles/{id}/eliminar','Configuracion\RoleController@eliminar');

    $router->get('empresas','Configuracion\EmpresaController@index');
    $router->post('empresas','Configuracion\EmpresaController@store');
    $router->get('empresas/{id}/edit','Configuracion\EmpresaController@edit');
    $router->post('empresas/{id}/eliminar','Configuracion\EmpresaController@eliminar');
    $router->post('empresas/{id}/activar','Configuracion\EmpresaController@activar');

    $router->get('puntoventas','Configuracion\PuntoVentaController@index');
    $router->post('puntoventas','Configuracion\PuntoVentaController@store');
    $router->get('puntoventas/{id}/edit','Configuracion\PuntoVentaController@edit');
    $router->post('puntoventas/{id}/eliminar','Configuracion\PuntoVentaController@eliminar');
    $router->post('puntoventas/{id}/activar','Configuracion\PuntoVentaController@activar');

    $router->get('usuarios','Configuracion\UsuarioController@index');
    $router->post('usuarios','Configuracion\UsuarioController@store');
    $router->get('usuarios/{id}/edit','Configuracion\UsuarioController@edit');
    $router->post('usuarios/{id}/eliminar','Configuracion\UsuarioController@eliminar');
    $router->post('usuarios/{id}/activar','Configuracion\UsuarioController@activar');
    $router->post('usuarios/exportar','Configuracion\UsuarioController@exportar');
    $router->post('usuarios/exportarPDF','Configuracion\UsuarioController@exportPDF'); // EJEMPLO DE COMO EXPORTAR PDF


    $router->get('message','Configuracion\UsuarioController@message');
    $router->post('message','Configuracion\UsuarioController@message');

    $router->get('usuarios/catalogos','Configuracion\UsuarioController@catalogos');
    $router->get('roles/catalogos','Configuracion\RoleController@catalogos');

    //CATALOGOS
    $router->get('marcas','Catalogos\MarcaController@index');
    $router->post('marcas','Catalogos\MarcaController@store');
    $router->get('marcas/{id}/edit','Catalogos\MarcaController@edit');
    $router->post('marcas/{id}/eliminar','Catalogos\MarcaController@eliminar');
    $router->post('marcas/exportar','Catalogos\MarcaController@exportar');

    $router->get('lineas','Catalogos\LineaController@index');
    $router->post('lineas','Catalogos\LineaController@store');
    $router->get('lineas/{id}/edit','Catalogos\LineaController@edit');
    $router->post('lineas/{id}/eliminar','Catalogos\LineaController@eliminar');
    $router->get('lineas/catalogos','Catalogos\LineaController@catalogos');
    $router->get('lineas/getLinea/{marcaid}','Catalogos\LineaController@getLinea');
    $router->post('lineas/exportar','Catalogos\LineaController@exportar');

    $router->get('categorias','Catalogos\CategoriaController@index');
    $router->post('categorias','Catalogos\CategoriaController@store');
    $router->get('categorias/{id}/edit','Catalogos\CategoriaController@edit');
    $router->post('categorias/{id}/eliminar','Catalogos\CategoriaController@eliminar');
    $router->post('categorias/exportar','Catalogos\CategoriaController@exportar');

    $router->get('proveedores','Catalogos\ProveedorController@index');
    $router->post('proveedores','Catalogos\ProveedorController@store');
    $router->get('proveedores/{id}/edit','Catalogos\ProveedorController@edit');
    $router->post('proveedores/{id}/eliminar','Catalogos\ProveedorController@eliminar');
    $router->post('proveedores/exportar','Catalogos\ProveedorController@exportar');

    $router->get('productos','Catalogos\ProductoController@index');
    $router->post('productos','Catalogos\ProductoController@store');
    $router->get('productos/{id}/edit','Catalogos\ProductoController@edit');
    $router->post('productos/{id}/eliminar','Catalogos\ProductoController@eliminar');
    $router->post('productos/{id}/activar','Catalogos\ProductoController@activar');
    $router->get('productos/catalogos','Catalogos\ProductoController@catalogos');
    $router->post('productos/exportar','Catalogos\ProductoController@exportar');

});

//Rutas no protegidas passwordchange
$router->post('api/usuarios/login','Configuracion\UsuarioController@login');
$router->post('api/usuarios/logout','Configuracion\UsuarioController@logout');
$router->post('api/usuarios/confirmacioncorreo','Configuracion\UsuarioController@confirmacioncorreo');
$router->post('api/usuarios/passwordchange/{token}','Configuracion\UsuarioController@passwordchange');
