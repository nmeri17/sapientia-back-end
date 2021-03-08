<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VideoStream extends Model {

    // use SoftDeletes;

    protected $table = "video_stream";

    protected $fillable = [
        'url'
    ];
}
