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

$router->get('/api/usuarios','Configuracion\UsuarioController@index');
$router->post('/api/usuarios','Configuracion\UsuarioController@store');
$router->get('/api/usuarios/{id}/edit','Configuracion\UsuarioController@edit');

$router->get('/api/usuarios/catalogos','Configuracion\UsuarioController@catalogos');
