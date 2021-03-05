<?php

namespace App\Http\Controllers;

class ReportsController extends Controller {

    function __construct() {
        # code...
    }

    public function index () {

        return view("reports.index");
    }
}
