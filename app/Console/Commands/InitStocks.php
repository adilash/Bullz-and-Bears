<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Stock;
use DB;

class InitStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:initStocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Stocks. (Resets Stocks table).';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function curl_get_contents($url){

        $ch = curl_init();
        $http_headers = array(
                    'Host: www.nseindia.com',
                    'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:55.0) Gecko/20100101 Firefox/55.0',
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: en-us,en;q=0.5',
                    'Connection: keep-alive'
                  );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $json = curl_exec($ch);
        curl_close($ch);
        return $json;

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
      //clear db
      DB::table('stocks')->delete();
      $res = $this->curl_get_contents("https://nseindia.com/live_market/dynaContent/live_watch/stock_watch/niftyStockWatch.json");
      $jsonobj = json_decode($res);
      $this->info('Fetch Complete: Time Stamp : '.$jsonobj->time);
      $update_time = date('Y-m-d H:i:s', strtotime($jsonobj->time));

      foreach($jsonobj->{'data'} as $data){

          if(str_replace(",", "",$data->ltP)!=0){
              $this->info("Inserting ".$data->symbol);
              Stock::create([
                    'symbol' => $data->symbol,
                    'name' => $data->symbol,//full name is not provided in the json.
                    'time_stamp' => $update_time,
                    'value' => str_replace(",","",$data->ltP),
                    'change' => str_replace(",","",$data->ptsC),
                    'daylow' => str_replace(",","",$data->low),
                    'dayhigh' => str_replace(",","",$data->high),
                    'weeklow' => str_replace(",","",$data->wklo),
                    'weekhigh'=> str_replace(",","",$data->wkhi),
                    'change_perc' => str_replace(",","",$data->per)
              ]);
          }
      }
    }
}
