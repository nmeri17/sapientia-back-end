<?php

namespace App\Http\Controllers;

use App\Models\Events;

use Illuminate\Http\Request;

class EventsController extends Controller {

    function __construct() {
    }

    public function index() {

        return response()->json([

            "data" => Events::all()
        ]);
    }

    public function create(Request $request) {

        //
    }

    public function store(Request $request) {

        $this->validate($request, [
            'name' => 'required|unique|min:8',
            'event_date' => 'required|date',
            'location' => 'required|min:8',
        ]);

        Events::create($request->all());

        return response()->json(["message" => "success"]);
    }

    public function show (Order $order) {
        //
    }

    public function edit( Order $order) {
        //
    }

    public function update(Request $request, Order $order) {

        //
    }
}

?>
