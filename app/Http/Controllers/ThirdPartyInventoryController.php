<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Models\Companies;
use App\Models\TPLInventoryItems;
use Carbon\Carbon;
// use Maatwebsite\Excel\Concerns\FromView;
use Excel;

// use App\Exports\InventoryExport;

class ThirdPartyInventoryController extends Controller
{
	public function index()
	{
		$companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();

        if(isset($companies->fulfillment_ids)){
            return view('layouts/thirdparty/thirdpartyinventory');
        }

        return redirect()->route('dashboard');
    }

	public function summary()
	{
        $companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();

        /* get access token */
        $client = new Client();
        $accessToken = null;
        try {
            $request = $client->request('POST', 'https://secure-wms.com/AuthServer/api/Token', [
                                'headers' => ['Authorization' => 'Basic Yzc5YWVjNjktNmE4ZC00ZDIyLTg2NmUtZGI3NjQ2ZTFiYTYxOnU1WEMrRTBWQVlZMGtDZnVYZFNtbTFheFhtcW5ZUnA4'],
                                'json' => [
                                'grant_type' => 'client_credentials',
                                'tpl' => '{e55a580d-29d1-43d0-9b7b-448c5602a223}',
                                'user_login_id' => '1'
            ]]);
            $response = json_decode($request->getBody());
            $accessToken = $response->access_token;
        } catch (\Exception $e) {
            print "Error1:";
        }
        /** End get access token **/

        $customerItemIds = array();

        if(isset($companies->fulfillment_ids)){

            $customerIds = explode(',',$companies->fulfillment_ids);

            foreach($customerIds as $customer){
                $apiRequest = 'https://secure-wms.com/customers/'.$customer.'/items?pgsiz=100&pgnum=1';

                try {
                    $request = $client->request('GET', $apiRequest, [
                        'headers' => [
                            'Authorization' => 'Bearer '.$accessToken,
                            'Accept' => "application/hal+json"
                        ],
                        'json' => []
                    ]);
                    $response = json_decode($request->getBody());
                    if($response){
                        $customerItems = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/customers/item'};
                        if($customerItems){
                            foreach($customerItems as $c){
                                $customerItemIds[] = $c->itemId;
                            }
                        }
                    }


                }catch (\Exception $e) {
                    //do something
                }
            }

            if(!empty($customerItemIds)){
                $TPLInventoryItems = TPLInventoryItems::where('companies_id',\Auth::user()->companies_id)->first();

                if(!$TPLInventoryItems){
                    $TPLInventoryItems = new TPLInventoryItems;
                    $TPLInventoryItems->companies_id = \Auth::user()->companies_id;
                }

                $TPLInventoryItems->item_ids = JSON_ENCODE($customerItemIds);
                $TPLInventoryItems->updated_at = Carbon::now();
                $TPLInventoryItems->save();
            }

            return view('layouts/thirdparty/thirdpartyinventorysummary');
        }

        return redirect()->route('dashboard');
    }

	public function thirdPartyInventoryDetail(Request $request)
	{
        $companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();
        $inventoryResult = array();
        $totalResults = 0;
        $staticLength = $_REQUEST['length'];
        $pageNumber = ceil($_REQUEST['start']/$staticLength) + 1;
        $apiSort = null;
        $columns = array(
            'itemIdentifier.sku',
            'itemDescription',
            'qualifier',
            'locationIdentifier.name',
            'serialNumber',
            'lotNumber',
            'expirationDate',
            'onHandQty',
            'availableQty',
        );

        if($_REQUEST['order']){
            if($_REQUEST['order'][0]['dir'] == 'asc'){
                $apiSort = '&sort=+'.$columns[$_REQUEST['order'][0]['column']];
            }else{
                $apiSort = '&sort=-'.$columns[$_REQUEST['order'][0]['column']];
            }
        }

        if($companies->fulfillment_ids){

            $client = new Client();
            /* get access token */
            $accessToken = null;
            try {
                $request = $client->request('POST', 'https://secure-wms.com/AuthServer/api/Token', [
                'headers' => ['Authorization' => 'Basic Yzc5YWVjNjktNmE4ZC00ZDIyLTg2NmUtZGI3NjQ2ZTFiYTYxOnU1WEMrRTBWQVlZMGtDZnVYZFNtbTFheFhtcW5ZUnA4'],
                'json' => [
                    'grant_type' => 'client_credentials',
                    'tpl' => '{e55a580d-29d1-43d0-9b7b-448c5602a223}',
                    'user_login_id' => '1'
                    ]]);
                $response = json_decode($request->getBody());
                $accessToken = $response->access_token;
            } catch (\Exception $e) {
                print "Error1:";
            }

            try {
            $request = $client->request('GET', 'https://secure-wms.com/inventory?pgsiz=75&pgnum='.$pageNumber.'&rql=customeridentifier.id=in=('.$companies->fulfillment_ids.')'.$apiSort, [
            'headers' => [
                'Authorization' => 'Bearer '.$accessToken,
                'Accept' => "application/hal+json"
            ],
            'json' => []
            ]);
            $response = json_decode($request->getBody());
            $totalResults = $response->totalResults;
            $inventory = $response->{'_embedded'};
            if($inventory){
                foreach ($inventory->item as $i) {
                    if(isset($i->expirationDate)){
                        $expDate = date('Y-m-d', strtotime($i->expirationDate));
                    }else{
                        $expDate = 'N/A';
                    }

                    if(isset($i->locationIdentifier)){
                        $location = $i->locationIdentifier->nameKey->name;
                    }else{
                        $location = 'N/A';
                    }

                    $inventoryResult[] = array(
                        $i->itemIdentifier->sku,
                        $i->itemDescription,
                        ($i->qualifier != '' ? $i->qualifier:'N/A'),
                        $location,
                        (isset($i->serialNumber) ? $i->serialNumber:'N/A'),
                        (isset($i->lotNumber) ? $i->lotNumber:'N/A'),
                        $expDate,
                        $i->onHandQty,
                        $i->availableQty
                    );
                }
            }

            } catch (\Exception $e) {
                print "Error2:";
                print $e->getMessage();
            }

        }

        $json_data = array(
            "draw"            => $_REQUEST['draw'],
            "recordsTotal"    => $totalResults,
            "recordsFiltered" =>$totalResults,
            "request" =>  $_REQUEST,
            "pageNumber" =>$pageNumber,
            "data"            => $inventoryResult
        );
        echo json_encode($json_data);
    }

	public function thirdPartyInventorySummary(Request $request)
	{
        $companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();
        $TPLInventoryItems = TPLInventoryItems::where('companies_id',\Auth::user()->companies_id)->first();

        $facilityId = 1;
        $inventoryResult = array();
        $totalResults = 0;
        $pageNumber = 1;
        $apiSort = null;
        $columns = array(
            'itemIdentifier.sku',
            // 'totalReceived',
            'onHold',
            'onHand',
            'allocated',
            'available',
            ''
        );

        if($companies->fulfillment_ids && $TPLInventoryItems){

            $tplCustIds = json_decode($TPLInventoryItems->item_ids,true);

            $client = new Client();
            /* get access token */
            $accessToken = null;
            try {
                $request = $client->request('POST', 'https://secure-wms.com/AuthServer/api/Token', [
                'headers' => ['Authorization' => 'Basic Yzc5YWVjNjktNmE4ZC00ZDIyLTg2NmUtZGI3NjQ2ZTFiYTYxOnU1WEMrRTBWQVlZMGtDZnVYZFNtbTFheFhtcW5ZUnA4'],
                'json' => [
                    'grant_type' => 'client_credentials',
                    'tpl' => '{e55a580d-29d1-43d0-9b7b-448c5602a223}',
                    'user_login_id' => '1'
                    ]]);
                $response = json_decode($request->getBody());
                $accessToken = $response->access_token;
            } catch (\Exception $e) {
                print "Error1:";
            }

            //get available skus
            $customerIds = explode(',',$companies->fulfillment_ids);

            foreach($customerIds as $cid){
                $apiRequestItems = 'https://secure-wms.com/customers/'.$cid.'/items?pgsiz=1&pgnum=1';
                try {
                    $request = $client->request('GET', $apiRequestItems, [
                        'headers' => [
                            'Authorization' => 'Bearer '.$accessToken,
                            'Accept' => "application/hal+json"
                        ],
                        'json' => []
                    ]);
                    $response = json_decode($request->getBody());
                    if($response){
                        $totalPages = ceil($response->totalResults/100);
                        $initial = 1;

                        while($initial <= $totalPages){
                            $req = 'https://secure-wms.com/customers/'.$cid.'/items?pgsiz=100&pgnum='.$initial;

                            try {
                                $request = $client->request('GET', $req, [
                                    'headers' => [
                                        'Authorization' => 'Bearer '.$accessToken,
                                        'Accept' => "application/hal+json"
                                    ],
                                    'json' => []
                                ]);

                                $response = json_decode($request->getBody());
                                if($response){
                                    $customerItems = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/customers/item'};
                                    if($customerItems){
                                        foreach($customerItems as $c){
                                                $customerItemData['s_'.$c->sku]['id'] = $c->itemId;
                                                $customerItemData['s_'.$c->sku]['sku'] = $c->sku;
                                                $customerItemData['s_'.$c->sku]['description'] = $c->description;
                                        }
                                    }
                                }
                            }catch (\Exception $e) {
                                    //do something
                            }
                            $initial++;
                        }
                    }

                }catch (\Exception $e) {
                    //do something
                }
            }



            try {
                $request = $client->request('GET', 'https://secure-wms.com/inventory/stocksummaries?pgsiz=1&pgnum='.$pageNumber.'&rql=facilityId=='.$facilityId, [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Accept' => "application/hal+json"
                ],
                'json' => []
            ]);
            } catch (\Exception $e) {
                print "Error2:";
                print $e->getMessage();
            }

            $response = json_decode($request->getBody());
            $totalResults = $response->totalResults;
            if($totalResults){
                $totalPages = ceil($totalResults/500);
                $initial = 1;

                while($initial <= $totalPages){
                    try {
                        $request = $client->request('GET', 'https://secure-wms.com/inventory/stocksummaries?pgsiz=500&pgnum='.$initial.'&rql=facilityId=='.$facilityId, [
                            'headers' => [
                                'Authorization' => 'Bearer '.$accessToken,
                                'Accept' => "application/hal+json"
                            ],
                            'json' => []
                        ]);
                        $response = json_decode($request->getBody());
                        $inventory = $response->{'summaries'};
                        if($inventory){
                            foreach ($inventory as $i) {
                                if(array_key_exists('s_'.$i->itemIdentifier->sku,$customerItemData)){
									if($customerItemData['s_'.$i->itemIdentifier->sku]['id'] == $i->itemIdentifier->id){
										$inventoryResult[] = array(
											$i->itemIdentifier->sku,
											$customerItemData['s_'.$i->itemIdentifier->sku]['description'],
											// $i->onHold,
											$i->onHand,
										   ($i->onHand - $i->available),
											$i->available,
											// ($i->onHand - $i->available),
										);
									}
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        print "Error2:";
                        print $e->getMessage();
                    }
                    $initial++;
                }
            }
        }

        $json_data = array(
            "recordsTotal"    => count($inventoryResult),
            "recordsFiltered" =>count($inventoryResult),
            "request" =>  $_REQUEST,
            "pageNumber" =>$pageNumber,
            "data"            => $inventoryResult
        );

        return response()->json($json_data);

	}

	public function exportInventory()
	{
		$companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();

		// return error if fullfillment_ids does not exists
		if(! $companies->fulfillment_ids) {
			return response()->json([
				'message' => 'Precondition Failed! No fulfillment ID found!'
			], 412);
		}

		/**
		 * request accessToken
		 */
		$accessToken = $this->requestAccessToken();


		/**
		 * fetch total results count
		 */
		$totalResults = $this->requestInventoryTotalResult($companies->fulfillment_ids, $accessToken);


		/**
		 * setup pagesize and page number
		 */
		if ($totalResults >= 500) {
			$pgsiz = 500;
		}
		else {
			$pgsiz = $totalResults;
		}

		$request_interval = number_format(ceil($totalResults/$pgsiz), 0);
		$pgnum = 1;

		/**
		 * fetch inventory items
		 */
		$inventoryResults = array();
		for ($i=0; $i<$request_interval; $i++)
		{
			$inv = $this->requestInventoryByChunk($companies->fulfillment_ids, $accessToken, $pgsiz, $pgnum);
			$inventoryResults = array_merge(
				$inventoryResults,
				$inv
			);
			$pgnum++;
		}
		/**
		 * sort reverse the item list
		 */
		$inventory = array_reverse($inventoryResults);

		array_unshift($inventory, [
			'SKU', 'Description', 'Qualifier', 'Location', 'Serial #', 'Lot #', 'Exp. Date', 'On Hand', 'Available',
		]);

		// dd($inventory);

		// guide: https://docs.laravel-excel.com/2.1/export/cells.html
		$currentTime = Carbon::now();
		Excel::create('inventory_'.$currentTime, function($excel) use ($inventory)
		{
			$excel->sheet('inventory', function($sheet) use ($inventory)
			{
				$sheet->setFontFamily('Calibri');

				$sheet->cells('A1:I1', function($cells) {
					$cells->setBorder(array(
						'left'   => array(
							'style' => 'thin',
							'color' => [
								'rgb' => 'ffffff'
							]
						),
					));
					$cells->setFont(array(
						'family'     => 'Calibri',
						'size'       => '13',
						'bold'       =>  true,
					));
					$cells->setFontColor('#ffffff');
					$cells->setValignment('center');
					$cells->setAlignment('center');
					$cells->setFontSize(13);
					$cells->setFontWeight('bold');
					$cells->setBackground('#455576');
				});

				$sheet->fromArray($inventory, null, 'A1', false, false);
			});
		})->download('xlsx');
	}

	public function exportSummary()
	{
        $companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();
		$TPLInventoryItems = TPLInventoryItems::where('companies_id',\Auth::user()->companies_id)->first();
		$facilityId = 1;

		// return error if fullfillment_ids does not exists
		if(!$companies->fulfillment_ids || !$TPLInventoryItems){
			return response()->json([
				'message' => 'Precondition Failed! No fulfillment ID found!'
			], 412);
		}

		$tplCustIds = json_decode($TPLInventoryItems->item_ids,true);

		/**
		 * request accessToken
		 */
		$accessToken = $this->requestAccessToken();

		//get available skus
		$customerIds = explode(',',$companies->fulfillment_ids);

		$client = new Client();

		foreach($customerIds as $cid){
			$apiRequestItems = 'https://secure-wms.com/customers/'.$cid.'/items?pgsiz=1&pgnum=1';
			try {
				$request = $client->request('GET', $apiRequestItems, [
					'headers' => [
						'Authorization' => 'Bearer '.$accessToken,
						'Accept' => "application/hal+json"
					],
					'json' => []
				]);
				$response = json_decode($request->getBody());
				if($response){
					$totalPages = ceil($response->totalResults/100);
					$initial = 1;

					while($initial <= $totalPages){
						$req = 'https://secure-wms.com/customers/'.$cid.'/items?pgsiz=100&pgnum='.$initial;

						try {
							$request = $client->request('GET', $req, [
								'headers' => [
									'Authorization' => 'Bearer '.$accessToken,
									'Accept' => "application/hal+json"
								],
								'json' => []
							]);

							$response = json_decode($request->getBody());
							if($response){
								$customerItems = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/customers/item'};
								if($customerItems){
									foreach($customerItems as $c){
											$customerItemData['s_'.$c->sku]['id'] = $c->itemId;
											$customerItemData['s_'.$c->sku]['sku'] = $c->sku;
											$customerItemData['s_'.$c->sku]['description'] = $c->description;
									}
								}
							}
						}catch (\Exception $e) {
								//do something
						}
						$initial++;
					}
				}

			}
			catch (\Exception $e) {
				print "Error:".$e->getMessage();
			}
		}


		try {
			$request = $client->request('GET', 'https://secure-wms.com/inventory/stocksummaries?pgsiz=1&pgnum=1&rql=facilityId=='.$facilityId, [
				'headers' => [
					'Authorization' => 'Bearer '.$accessToken,
					'Accept' => "application/hal+json"
				],
				'json' => []
			]);
		}
		catch (\Exception $e) {
			print "Error2:";
			print $e->getMessage();
		}

		$response = json_decode($request->getBody());
		$totalResults = $response->totalResults;
		if($totalResults){
			$totalPages = ceil($totalResults/500);
			$initial = 1;

			while($initial <= $totalPages){
				try {
					$request = $client->request('GET', 'https://secure-wms.com/inventory/stocksummaries?pgsiz=500&pgnum='.$initial.'&rql=facilityId=='.$facilityId, [
						'headers' => [
							'Authorization' => 'Bearer '.$accessToken,
							'Accept' => "application/hal+json"
						],
						'json' => []
					]);
					$response = json_decode($request->getBody());
					$inventory = $response->{'summaries'};
					if($inventory){
						foreach ($inventory as $i) {
							if(array_key_exists('s_'.$i->itemIdentifier->sku,$customerItemData)){
								$inventoryResult[] = array(
									$i->itemIdentifier->sku,
									$customerItemData['s_'.$i->itemIdentifier->sku]['description'],
									// $i->onHold,
									$i->onHand,
								   ($i->onHand - $i->available),
									$i->available,
									// ($i->onHand - $i->available),
								);
							}
						}
					}
				} catch (\Exception $e) {
					print "Error2:";
					print $e->getMessage();
				}
				$initial++;
			}
		}

		$inventory = array_reverse($inventoryResult);

		array_unshift($inventory, [
			'SKU', 'Description', 'On Hand', 'Allocated', 'Available'
		]);


		$currentTime = Carbon::now();
		Excel::create('inventory_'.$currentTime, function($excel) use ($inventory)
		{
			$excel->sheet('inventory', function($sheet) use ($inventory)
			{
				$sheet->setFontFamily('Calibri');

				$sheet->cells('A1:E1', function($cells) {
					$cells->setBorder(array(
						'left'   => array(
							'style' => 'thin',
							'color' => [
								'rgb' => 'ffffff'
							]
						),
					));
					$cells->setFont(array(
						'family'     => 'Calibri',
						'size'       => '13',
						'bold'       =>  true,
					));
					$cells->setFontColor('#ffffff');
					$cells->setValignment('center');
					$cells->setAlignment('center');
					$cells->setFontSize(13);
					$cells->setFontWeight('bold');
					$cells->setBackground('#455576');
				});

				$sheet->fromArray($inventory, null, 'A1', false, false);
			});
		})->download('xlsx');

	}

	private function requestAccessToken()
	{
		$client = new Client();
		$accessToken = null;

		try {
			$request = $client->request('POST', 'https://secure-wms.com/AuthServer/api/Token', [
				'headers' => ['Authorization' => 'Basic Yzc5YWVjNjktNmE4ZC00ZDIyLTg2NmUtZGI3NjQ2ZTFiYTYxOnU1WEMrRTBWQVlZMGtDZnVYZFNtbTFheFhtcW5ZUnA4'],
				'json' => [
					'grant_type' => 'client_credentials',
					'tpl' => '{e55a580d-29d1-43d0-9b7b-448c5602a223}',
					'user_login_id' => '1'
				]
			]);
			$response = json_decode($request->getBody());
			$accessToken = $response->access_token;

			return $accessToken;
		}
		catch (\Exception $e) {
			print "Error1:".$e->getMessage();
		}
	}

	private function requestInventoryTotalResult($fulfillment_ids, $accessToken)
	{
		$client = new Client();

		$totalResults = 0;

		try {
			$request = $client->request('GET', 'https://secure-wms.com/inventory?pgsiz=1&pgnum=1&rql=customeridentifier.id=in=('.$fulfillment_ids.')', [
				'headers' => [
					'Authorization' => 'Bearer '.$accessToken,
					'Accept' => "application/hal+json"
				],
				'json' => []
			]);
			$response = json_decode($request->getBody());
			$totalResults = $response->totalResults;

			return $totalResults;
		}
		catch (\Exception $e) {
			print "Error2:";
			print $e->getMessage();
		}
	}

	private function requestInventoryByChunk($fulfillment_ids, $accessToken, $pgsiz, $pgnum)
	{
		$client = new Client();

		try {
			$request = $client->request('GET', 'https://secure-wms.com/inventory?pgsiz='.$pgsiz.'&pgnum='.$pgnum.'&rql=customeridentifier.id=in=('.$fulfillment_ids.')', [
				'headers' => [
					'Authorization' => 'Bearer '.$accessToken,
					'Accept' => "application/hal+json"
				],
				'json' => []
			]);
			$response = json_decode($request->getBody());
			$totalResults = $response->totalResults;
			$inventory = $response->{'_embedded'};
			// dd($totalResults);
			// dd($inventory);

			if($inventory){
				foreach ($inventory->item as $i) {
					if(isset($i->expirationDate)){
						$expDate = date('Y-m-d', strtotime($i->expirationDate));
					}else{
						$expDate = 'N/A';
					}

					if(isset($i->locationIdentifier)){
						$location = $i->locationIdentifier->nameKey->name;
					}else{
						$location = 'N/A';
					}

					$inventoryResult[] = array(
						$i->itemIdentifier->sku,
						$i->itemDescription,
						($i->qualifier != '' ? $i->qualifier:'N/A'),
						$location,
						(isset($i->serialNumber) ? $i->serialNumber:'N/A'),
						(isset($i->lotNumber) ? $i->lotNumber:'N/A'),
						$expDate,
						$i->onHandQty,
						$i->availableQty
					);
				}
			}
			// dd($inventoryResult);
			return $inventoryResult;

		}
		catch (\Exception $e) {
			print "Error2:";
			print $e->getMessage();
		}
	}

}
