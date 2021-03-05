<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model {

    // use SoftDeletes;

    protected $table = "clients";

    protected $fillable = [
        'address_id', 'name', 'phone'
    ];

    public function address() {

        return $this->belongsTo(Address::class);
    }
}
