<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This project currently uses Filament as its canonical application flow.
| There is no public REST API exposed from this codebase at the moment.
| Keep this file intentionally empty until dedicated API controllers and
| request validation are introduced.
|
*/

use Illuminate\Support\Facades\Route;

Route::get('/docs', function () {
    return redirect('/api/documentation');
})->name('api.docs');
