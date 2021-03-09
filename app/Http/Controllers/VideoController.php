<?php

namespace App\Http\Controllers;

use App\Models\VideoStream;

use Illuminate\Http\Request;

class VideoController extends Controller {

    function __construct() {
        # code...
    }

    public function index () {

        return response()->json([
            "data" => VideoStream::latest()->first()
        ]);
    }

    public function store(Request $request) {

        $this->validate($request, [
            'url' => 'required|url|min:8'
        ]);

        VideoStream::create($request->all());

        return response()->json(["message" => "success"]);
    }
}
