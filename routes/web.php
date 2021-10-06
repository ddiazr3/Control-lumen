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
});

//Rutas no protegidas
$router->post('api/usuarios/login','Configuracion\UsuarioController@login');
