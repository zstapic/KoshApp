<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

//use eloquent(similar to entity framework), DB is just for quick database connection test

Route::get('/', function () {
    return response()->json(["message" => "Connection successful"]);    
});

Route::get('/users', function () {
    return DB::table("demo_users")->get();
});
