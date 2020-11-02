<?php

Route::group(['prefix' => 'blogs', 'middleware' => []], function () {
    Route::get('/', 'BlogController@index')->name('blogs.index');
    Route::post('/', 'BlogController@store')->name('blogs.create');
    Route::get('/{blog}', 'BlogController@show')->name('blogs.read');
    Route::put('/{blog}', 'BlogController@update')->name('blogs.update');
    Route::delete('/{blog}', 'BlogController@destroy')->name('blogs.delete');
});