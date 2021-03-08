<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
/*

  setActiveYoutubeLink = "$basePath/video-stream/store",

  getActiveYoutubeLink = "$basePath/video-stream
  */
Route::resource('/events', "EventsController");

Route::resource('/video-stream', "VideoController"); // create endpoints, write blocs consuming the endpoints, interact with the bloc in the view
