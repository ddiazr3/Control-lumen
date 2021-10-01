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


$router->get('/api/roles','Configuracion\RoleController@index');
$router->post('/api/roles','Configuracion\RoleController@store');
$router->get('/api/roles/{id}/edit','Configuracion\RoleController@edit');
$router->post('/api/roles/{id}/eliminar','Configuracion\RoleController@eliminar');

$router->get('/api/usuarios','Configuracion\UsuarioController@index');
$router->post('/api/usuarios','Configuracion\UsuarioController@store');
$router->get('/api/usuarios/{id}/edit','Configuracion\UsuarioController@edit');
$router->post('/api/usuarios/{id}/eliminar','Configuracion\UsuarioController@eliminar');
$router->post('/api/usuarios/{id}/activar','Configuracion\UsuarioController@activar');
$router->post('/api/usuarios/exportar','Configuracion\UsuarioController@exportar');
$router->post('/api/usuarios/exportarPDF','Configuracion\UsuarioController@exportPDF'); // EJEMPLO DE COMO EXPORTAR PDF

$router->get('/api/message','Configuracion\UsuarioController@message');
$router->post('/api/message','Configuracion\UsuarioController@message');

$router->get('/api/usuarios/catalogos','Configuracion\UsuarioController@catalogos');
$router->get('/api/roles/catalogos','Configuracion\RoleController@catalogos');
