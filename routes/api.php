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
Route::post('content/{id}',              ['uses' => 'controller@content' ]);

//RICERCA
Route::post('contents',                 ['uses' => 'controller@contents' ]);

Route::get('subcategories/{id?}',       ['uses' => 'controller@subcategories']);

Route::get('categories',                ['uses' => 'controller@categories']);

Route::post('boxes',                    ['uses' => 'controller@boxes' ]);

Route::post('box',                    ['uses' => 'controller@box' ]);