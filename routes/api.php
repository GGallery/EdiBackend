<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//CONTENUTO SINGOLO
Route::post('content/{id}',              ['uses' => 'Controller@content' ]);

//RICERCA
Route::post('contents',                 ['uses' => 'Controller@contents' ]);

Route::get('content/{id?}',             ['uses' => 'Controller@content']);

Route::get('subcategories/{id?}',       ['uses' => 'Controller@subcategories']);

Route::get('categories/{id?}',                ['uses' => 'Controller@categories']);

Route::post('boxes',                    ['uses' => 'Controller@boxes' ]);

Route::post('box',                    ['uses' => 'Controller@box' ]);

Route::get('test/{id}',              ['uses' => 'Controller@content' ]);