<?php

namespace Increment\Imarket\Merchant\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Merchant\Models\Location;
use Carbon\Carbon;

class LocationController extends APIController
{
    //
    function __construct(){
        $this->model = new Location();
        // $this->notRequired = array(
        //     'name', 'address', 'prefix', 'logo', 'website', 'email'
        // );
      }
}
