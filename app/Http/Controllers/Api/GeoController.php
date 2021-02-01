<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Geo\Country;
use App\Models\Geo\Province;
use Illuminate\Http\Request;

class GeoController extends Controller
{
    const TYPE_COUNTRY = Country::class;
    const TYPE_PROVINCE = Province::class;
    
    public function index()
    {

    }
}
