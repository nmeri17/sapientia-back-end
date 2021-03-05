<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model {

    // use SoftDeletes;

    protected $table = "orders";

    protected $fillable = [
        'sender_id', 'receiver_id', 'description', 'weight', 'quantity', 'item_worth', 'fragile', 'express_delivery', "billing_number", "price", "approved", "agent_id"
    ];

    public function sender() {

        return $this->belongsTo(Client::class, "sender_id");
    }

    public function receiver() {

        return $this->belongsTo(Client::class, "receiver_id");
    }

    public function agent() {

        return $this->belongsTo(User::class, "agent_id");
    }
}
