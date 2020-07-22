<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class DeleteReceiverItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:receiverItems';

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
        $receiverIds = [212809,212825,212810,212807,212805,212804];
        
        foreach($receiverIds as $receiver){

            $accessToken = $this->getAccesstoken();

            // Get Etag For Receiver Item
            $client = new Client();
            try {
                $request = $client->request('GET', 'https://secure-wms.com/inventory/receivers/'.$receiver.'/items?detail=none', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Accept' => "application/hal+json"
                ],
                'json' => []
                ]);
                $headers = $request->getHeaders();
                $etag = $headers['ETag'][0];
            } catch (\Exception $e) {
                $response = $e->getResponse();
                $responseBodyAsString = $response->getBody()->getContents();
                print $responseBodyAsString;
            }

            // Unconfirm
            $client = new Client();
            try {
                $request = $client->request('POST', 'https://secure-wms.com/inventory/receivers/'.$receiver.'/unconfirmer', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Accept' => "application/hal+json",
                    'If-Match' => $etag
                ],
                'json' => []
                ]);
                $headers = $request->getHeaders();
                $response = json_decode($request->getBody());
                if($response){
                    // Delete Items
                    $client = new Client();
                    try {
                        $request = $client->request('DELETE', 'https://secure-wms.com/inventory/receivers/'.$receiver.'/items', [
                        'headers' => [
                            'Authorization' => 'Bearer '.$accessToken,
                            'Accept' => "application/hal+json",
                            'If-Match' => $etag
                        ],
                        'json' => []
                        ]);
                        $headers = $request->getHeaders();
                        $response = json_decode($request->getBody());
                    } catch (\Exception $e) {
                        $response = $e->getResponse();
                        $responseBodyAsString = $response->getBody()->getContents();
                        print $responseBodyAsString;
                    }
                }
            } catch (\Exception $e) {
                $response = $e->getResponse();
                $responseBodyAsString = $response->getBody()->getContents();
                print $responseBodyAsString;
            }
        }
    }

    private function getAccesstoken(){
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

        return $accessToken;
    }
}
