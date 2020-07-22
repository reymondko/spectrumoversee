<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use App\Models\Companies;
use App\Libraries\Logiwa\LogiwaAPI;
use Carbon\Carbon;
use App\Models\LogiwaDepositor;
use Excel;

class LogiwaInventoryController extends Controller
{
    /**
     * Returns a consolidated list of Inventory
     * based on the user depositor IDs
     */
    public function summary(){
        $inventory_summary = [];
        $depositors = $this->getDepositors();

        foreach($depositors as $depositor){
            $body = [];
            $body['DepositorID'] = $depositor->logiwa_depositor_id;
            $body['DepositorCode'] = $depositor->logiwa_depositor_code;
            $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
            $logiwa = new LogiwaAPI;
            $request = $logiwa->getConsolidatedInventoryReport($body);
            if($request['success'] == true){
                if(isset($request['data']->Data)){
                    foreach($request['data']->Data as $inventory){
                        $inventory_summary[] = $inventory;
                    }
                }
            }
        }

        return view('layouts/logiwa/logiwainventorysummary')->with('inventory_summary',$inventory_summary);
    }

    /**
     * Generates an excel file of the inventory summary
     *
     * @return Excel
     */
    public function exportSummary(){
        $inventory_summary = [];
        $depositors = $this->getDepositors();

        // Define Export Headers
        $inventory_summary[] = [
            'Company',
            'SKU',
            'Description',
            'Stock Quantity',
            'Damaged',
            'Undamaged',
            'Pack Quantity'
        ];

        foreach($depositors as $depositor){
            $body = [];
            $body['DepositorID'] = $depositor->logiwa_depositor_id;
            $body['DepositorCode'] = $depositor->logiwa_depositor_code;
            $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
            $logiwa = new LogiwaAPI;
            $request = $logiwa->getConsolidatedInventoryReport($body);
            if($request['success'] == true){
                if(isset($request['data']->Data)){
                    foreach($request['data']->Data as $inventory){
                        $inventory_summary[] = [
                            $inventory->DepositorDescription,
                            $inventory->InventoryItemDescription,
                            $inventory->Description,
                            $inventory->StockQty,
                            $inventory->Damaged,
                            $inventory->Undamaged,
                            $inventory->PackQuantity
                        ];
                    }
                }
            }
        }

        $currentTime = Carbon::now();

        Excel::create('inventory_'.$currentTime, function($excel) use ($inventory_summary)
		{
			$excel->sheet('inventory', function($sheet) use ($inventory_summary)
			{
				$sheet->setFontFamily('Calibri');

				$sheet->cells('A1:G1', function($cells) {
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

				$sheet->fromArray($inventory_summary, null, 'A1', false, false);
			});
		})->download('xlsx');
    }

    /**
     * Retrieve user customer code for logiwa
     */
    private function getDepositors(){
        $depositor_array = [];
        $depositors = LogiwaDepositor::where('companies_id',\Auth::user()->companies_id)->get();
        foreach($depositors as $depositor){
            $depositor_array[] = $depositor;
        }
        return $depositor_array;
    }
}
