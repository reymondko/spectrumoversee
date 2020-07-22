<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\Models\InventoryItemScans;
use App\Models\Inventory;
use App\Models\Locations;
use App\Models\Companies;
use Illuminate\Support\Facades\Gate;
use App\Models\TPLOrderMonthlyReport;

class ThirdPartyDashboardController extends Controller
{
    public function index(){

        // $companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();
        // if(isset($companies->fulfillment_ids)){
        if(Gate::allows('company-with-third-party', auth()->user())){
            $tmp = array();

            $currentMonth = Carbon::now()->format('M');
            $currentYear  = Carbon::now()->year;
            $compMonthlyReport = TPLOrderMonthlyReport::where('companies_id',\Auth::user()->companies_id)
                                                              ->where('year',$currentYear)
                                                              ->where('month',$currentMonth)
                                                              ->first();

            if($compMonthlyReport){
                $orders = json_decode($compMonthlyReport->data);
                $ctr = 0;
                $orders = array_reverse($orders);
                foreach($orders as $order){
                    $creationDate = explode('T',$order->creationDate);
                    $dateArrIndex = $creationDate[0];
                    if(array_key_exists($dateArrIndex,$tmp)){
                        $tmp[$dateArrIndex]['order_count'] = $tmp[$dateArrIndex]['order_count'] + 1;
                        if($order->isClosed == 1){
                            $tmp[$dateArrIndex]['shipped'] = $tmp[$dateArrIndex]['shipped'] + 1;
                        }
                    }else{

                        $date = date_create($dateArrIndex);

                        $tmp[$dateArrIndex]['date_name'] = date_format($date,'M d');
                        $tmp[$dateArrIndex]['order_count'] = 1;
                        $tmp[$dateArrIndex]['shipped'] = 0;
                        if($order->isClosed == 1){
                            $tmp[$dateArrIndex]['shipped'] = 1;
                        }
                    }
                }
            }

            $graphData = $tmp;


            $inventoryLocations = Locations::select('name','tpl_customer_id')
                                           ->where('tpl_customer_id', explode(',', \Auth::user()->companies->fulfillment_ids))
                                           ->get();

            $invByLocation = Inventory::select('last_scan_location', DB::raw('count(*) as total'))
                                 ->whereIn('inventory.tpl_customer_id', explode(',', \Auth::user()->companies->fulfillment_ids))
                                 ->groupBy('last_scan_location')
                                 ->where('last_scan_type','incoming')
                                 ->where('deleted',0)
                                 ->get();

            $invByLocationTransit = Inventory::select('last_scan_location', DB::raw('count(*) as total'))
                                 ->whereIn('inventory.tpl_customer_id', explode(',', \Auth::user()->companies->fulfillment_ids))
                                 ->where('last_scan_type','outgoing')
                                 ->where('deleted',0)
                                 ->groupBy('last_scan_location')
                                 ->get();

            $locationStats = array();
            foreach($inventoryLocations as $i){
                $locationStats[$i->name]['total'] = 0;
                $locationStats[$i->name]['transit'] = 0;

                foreach($invByLocation as $ibl){
                    if($ibl->last_scan_location == $i->name){
                        $locationStats[$i->name]['total'] = $ibl->total;
                        break;
                    }
                }

                foreach($invByLocationTransit as $iblt){
                    if($iblt->last_scan_location == $i->name){
                        $locationStats[$i->name]['transit'] = $iblt->total;
                        break;
                    }
                }

            }

            $locationColors = array('#fcb55e',
                                     '#f88b01',
                                     '#485880',
                                     '#27304d',
                                     '#fcb55e',
                                     '#f88b01',
                                     '#485880',
                                     '#27304d',
                                     '#fcb55e',
                                     '#f88b01',
                                     '#485880',
                                     '#27304d',
                                     '#fcb55e',
                                     '#f88b01',
                                     '#485880',
                                     '#27304d',
                                     '#fcb55e',
                                     '#f88b01',
                                     '#485880',
                                     '#27304d',
                                     '#fcb55e',
                                     '#f88b01',
                                     '#485880',
                                     '#27304d'
                                    );

          $lastScannedItems = InventoryItemScans::with('user:id,name','inventory')
                                                ->join('inventory', 'inventory_item_scans.inventory_item_id', '=', 'inventory.id')
                                                ->whereIn('inventory.tpl_customer_id', explode(',', \Auth::user()->companies->fulfillment_ids))
                                                ->orderByDesc('inventory_item_scans.created_at')
                                                ->limit(10)
                                                ->get();


            $data = array(
                'location_stats' => $locationStats,
                'location_colors' => $locationColors,
                'latest_scan' => $lastScannedItems
            );

            return view('layouts/thirdparty/thirdpartydashboard',compact('graphData', 'data'));

        }

        return redirect()->route('dashboard');
    }
}
