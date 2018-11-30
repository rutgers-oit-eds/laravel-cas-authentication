<?php

/* CAS Authentication Routes */

Route::group(['middleware' => ['web'], 'namespace' => 'Rutgers\Cas'], function () {
    Route::get('/login', 'CasLoginController@login')->name('login');
    Route::post('/logout', 'CasLoginController@logout')->name('logout');
    Route::get('/auth/sso_logout', 'CasLoginController@cas_logout');
});