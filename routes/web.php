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

/*$router->post('/api/usuarios', function (Request $request) {


    $rules    = [
        'usuario.nombre'    => 'required',
        'usuario.apellido'  => 'required',
        'usuario.correo'    => 'required|email',
        'usuario.dpi'       => 'required',
        'usuario.telefono'  => 'required',
        'usuario.direccion' => 'required',
        'usuario.empresaid' => 'required|min:1',
        'usuario.rolesid'   => 'required|min:1'
    ];

    $messages = [
        'usuario.nombre'        => 'El nombre es requerido',
        'usuario.apellido'      => 'El apellido es requerido',
        'usuario.correo'        => 'El correo es requerida',
        'usuario.dpi'           => 'El dpi es requerido',
        'usuario.telefono'      => 'El telefono es requerido',
        'usuario.direccion'     => 'La direccion es requerida',
        'usuario.empresaid'     => 'La empresa es requerida',
        'usuario.empresaid.min' => 'Al menos debe seleccionar una empresa',
        'usuario.rolesid'       => 'El rol es requerido',
        'usuario.rolesid.min'   => 'Al menos debe seleccionar un rol'
    ];

   // $this->validate($rules, $messages);

    //$this->validate($request, $rules);

    $validator = Validator::make($request, $rules, $messages);

    // Store User...
});*/

$router->get('/api/usuarios/catalogos','Configuracion\UsuarioController@catalogos');
