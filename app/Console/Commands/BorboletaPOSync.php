<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Libraries\Logiwa\LogiwaAPI;
use Illuminate\Support\Facades\Gate;
use App\Models\Companies;
use DB;

class BorboletaPOSync extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'BorboletaPOSync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $client = new Client([
        'headers' => [
          'Authorization' => '8cbc86f7b8ee6a7ed436516c25961fa92fdc2aed662eda7cdaf92c78b0f53ee9',
          'Account' => 'A2451'
        ]
      ]);

      $r = $client->request('GET', 'https://app.inventory-planner.com/api/v1/purchase-orders?last_modified_sort=desc&limit=50');
      $response = $r->getBody()->getContents();
      $decoded = json_decode($response);

      print_r($decoded);
      exit;
    }
}
