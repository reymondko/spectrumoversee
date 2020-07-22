<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\InventoryItemScans;
use App\Models\Inventory;
use App\Models\Locations;
use App\Models\LogiwaDepositor;
use DB;


class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::allows('user-only', auth()->user()) || Gate::allows('company-only', auth()->user())) {

            $depositorIds = $this->getLogiwaDepositorIds(\Auth::user()->companies->id);

            if(!$depositorIds){
                $depositorIds = [];
            }

            $locationStats = array();
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
            if($depositorIds){
                $inventoryLocations = Locations::select('name','tpl_customer_id')
                                            ->whereIn('tpl_customer_id', $depositorIds)
                                            ->get();

                $invByLocation = Inventory::select('last_scan_location', DB::raw('count(*) as total'))
                                    ->whereIn('inventory.tpl_customer_id', $depositorIds)
                                    ->groupBy('last_scan_location')
                                    ->where('last_scan_type','incoming')
                                    ->where('deleted',0)
                                    ->get();

                $invByLocationTransit = Inventory::select('last_scan_location', DB::raw('count(*) as total'))
                                    ->whereIn('inventory.tpl_customer_id', $depositorIds)
                                    ->where('last_scan_type','outgoing')
                                    ->where('deleted',0)
                                    ->groupBy('last_scan_location')
                                    ->get();

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



                $lastScannedItems = InventoryItemScans::with('user:id,name','inventory')
                                                ->join('inventory', 'inventory_item_scans.inventory_item_id', '=', 'inventory.id')
                                                ->whereIn('inventory.tpl_customer_id', $depositorIds)
                                                ->orderByDesc('inventory_item_scans.created_at')
                                                ->limit(10)
                                                ->get();
            }



            $data = array(
                'location_stats' => $locationStats,
                'location_colors' => $locationColors,
                'latest_scan' => $lastScannedItems
            );

            return view('layouts/dashboard/dashboard')->with('data', $data);
        }elseif(Gate::allows('tpl-only', auth()->user())){
            return redirect('/thirdparty/dashboard');
        }

        return view('layouts/dashboard/dashboard');
    }

    /**
     * Retrieves company depositor data
     *
     * @return App\Models\LogiwaDepositor
     */
    private function getLogiwaDepositorIds($companyId){
        $logiwaDepositors = LogiwaDepositor::where('companies_id',$companyId)->get()->pluck('logiwa_depositor_id')->toArray();
        return $logiwaDepositors;
    }
}
