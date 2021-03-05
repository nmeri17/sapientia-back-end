<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model {

    // use SoftDeletes;

    protected $table = "address";

    protected $fillable = [
        'city_id', 'name'
    ];

    public function city () {

        return $this->belongsTo(Cities::class, 'city_id');
    }
}
