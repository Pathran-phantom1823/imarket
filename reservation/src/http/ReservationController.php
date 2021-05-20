<?php

namespace Increment\Imarket\Reservation\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use App\TopChoice;
use Increment\Imarket\Reservation\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class ReservationController extends APIController
{

	public $synqtClass = 'App\Http\Controllers\SynqtController';
    public $merchantClass = 'Increment\Imarket\Merchant\Http\MerchantController';
	public $messengerGroupClass = 'Increment\Messenger\Http\MessengerGroupController';
	public $ratingClass = 'Increment\Common\Rating\Http\RatingController';

   	function __construct(){
   		$this->model = new Reservation();
   	}

	public function retrieve(Request $request){
		$data = $request->all();
		$con = $data['condition'];
		$result = Reservation::where($con[0]['column'], $con[0]['clause'], $con[0]['value'])
			->where($con[1]['column'], $con[1]['clause'], $con[1]['value'])->offset($data['offset'])->limit($data['limit'])->get();
		if(sizeof($result) > 0){
			$i = 0;
			foreach ($result as $key) {
				$result[$i]['reservee'] = $this->retrieveNameOnly($result[$i]['account_id']);
				$result[$i]['synqt'] = app($this->synqtClass)->retrieveByParams('id', $result[$i]['payload_value']);
                $result[$i]['merchant'] = app($this->merchantClass)->getByParams('id', $result[$i]['merchant_id']);
				$result[$i]['rating'] = app($this->ratingClass)->getRatingByPayload('merchant_id', $result[$i]['merchant_id']);
				$result[$i]['date_time_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['datetime'])->copy()->tz($this->response['timezone'])->format('F j, Y H:i A');
				$result[$i]['members'] = app($this->messengerGroupClass)->getMembersByParams('payload', $result[$i]['payload_value'], ['id', 'title']);
			 $i++;
			}
			$this->response['data'] = $result;
		}
		return $this->response();
	}

	public function create(Request $request){
		$data = $request->all();
		$this->model = new Reservation();
		$this->insertDB($data);
		if($this->response['data'] !== null){
			TopChoice::where('synqt_id', '=', $data['payload_value'])->update(array(
				'deleted_at' => Carbon::now()
			));
		}

		return $this->response();

	}

	public function retrieveByParams($whereArray, $returns){
		$result = Reservation::where($whereArray)->get($returns);
		return sizeof($result) > 0 ? $result[0] : null;
	}
}
