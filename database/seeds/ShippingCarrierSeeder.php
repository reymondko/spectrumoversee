<?php

use Illuminate\Database\Seeder;
use App\Models\ShippingCarriers;
use App\Models\ShippingCarrierMethods;
use Carbon\Carbon;

class ShippingCarrierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $upsCarrier = ShippingCarriers::Create([
            'name' => 'UPS'
        ]);
        
        $upsMethods = array(
            array('value'=>'01','name'=>'UPS Next Day Air','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'14','name'=>'UPS Next Day Air Early','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'13','name'=>'UPS Next Day Air Saver','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'02','name'=>'UPS 2nd Day Air','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'59','name'=>'UPS 2nd Day Air A.M.','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'12','name'=>'UPS 3 Day Select','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'54','name'=>'UPS Ground','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'07','name'=>'UPS Worldwide Express','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'65','name'=>'UPS Worldwide Express Saver','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'08','name'=>'UPS Worldwide Expedited','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'21','name'=>'UPS Economy','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'11','name'=>'UPS Standard','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'#02','name'=>'UPS Expedited','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'#13','name'=>'UPS Express Saver','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'#01','name'=>'UPS Express','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'#54','name'=>'UPS Express Plus','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'#11','name'=>'UPS Standard Canada','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'#12','name'=>'UPS 3 Day Select (Canada)','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'USPL','name'=>'UPS SurePost Less than 1 lb','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'USPG','name'=>'UPS SurePost 1 lb or Greater','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'USPB','name'=>'UPS SurePost BPM','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'USPM','name'=>'UPS SurePost Media','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'UMIF','name'=>'UPS First Class Mail','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'UMIP','name'=>'UPS Priority Mail','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'UMIX','name'=>'UPS Expedited Mail Innovations','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'UMIM','name'=>'UPS Priority Mail Innovations','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'UMIE','name'=>'UPS Economy Mail Innovations','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'UPSGFP','name'=>'UPS Ground with Freight Pricing','shipping_carriers_id' => $upsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $fedExCarrier = ShippingCarriers::Create([
            'name' => 'FedEx'
        ]);

        $fedExMethods = array(
            array('value'=>'F06','name'=>'FedEx First Overnight','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'F05','name'=>'FedEx Standard Overnight','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'F03','name'=>'FedEx 2Day','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'F20','name'=>'FedEx Express Saver','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'F70','name'=>'FedEx 1Day Freight','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'F80','name'=>'FedEx 2Day Freight','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'F83','name'=>'FedEx 3Day Freight','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'F92','name'=>'FedEx Ground','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'F90','name'=>'FedEx Home Delivery','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'F07','name'=>'FedEx Extra Hours (Discontinued, do not use!)','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'I06','name'=>'FedEx International First','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'I01','name'=>'FedEx International Priority','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'I03','name'=>'FedEx International Economy','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'I07','name'=>'FedEx Extra Hours (Discontinued, do not use!!!)','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'I70','name'=>'FedEx International Priority Freight','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'I86','name'=>'FedEx International Economy Freight','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'I92','name'=>'FedEx International Ground','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FSP','name'=>'FedEx SmartPost','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'F2A','name'=>'FedEx 2Day A.M.','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FFF','name'=>'FedEx First Overnight Freight','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FPF','name'=>'FedEx Freight Priority','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FEF','name'=>'FedEx Freight Economy','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FEC','name'=>'FedEx Econom','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'MVP','name'=>'MailView Over 4.4 pounds','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FPRM','name'=>'Premium FIMS','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FO4L','name'=>'FIMS over 4.4 Pound','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FSTD','name'=>'Standard FIMS','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FDXEUFIRST','name'=>'FedEx Europe First','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FDXND9AM','name'=>'FedEx Next Day by 9am','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FDXND10AM','name'=>'FedEx Next Day by 10am','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FDXNDNOON','name'=>'FedEx Next Day by 12 noon','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FDXNDEOD','name'=>'FedEx Next Day','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FIPE','name'=>'FedEx service stub per ZF Case 53302','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FDD','name'=>'FedEx Distance Deferred','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FNDF','name'=>'FedEx Next Day Freight','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'MVL','name'=>'MailView Lite','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FDXIGC','name'=>'FedEx IGC','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FDXIPE','name'=>'FedEx International Priority Express','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FDXINTLDDPRIORITY','name'=>'FedEx International Priority DirectDistribution','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FDXINTLDDPRIORITYFREIGHT','name'=>'FedEx International Priority DirectDistributon Freight','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FDXINTLDD','name'=>'FedEx International DirectDistribution','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FDXINTLDDGROUND','name'=>'FedEx International Ground Distribution','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FDXINTLDDSURFACE','name'=>'FedEx International DirectDistribution Surface Solutions U.S. to Canada','shipping_carriers_id'=>$fedExCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now())
        );
        
        $dhlCarrier = ShippingCarriers::Create([
            'name' => 'DHL'
        ]); 
        
        $dhlCarrierMethods = array(
            array('value'=>'AEE','name'=>'DHL Next Day 10:30 am','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'AE','name'=>'DHL Next Day 12:00 pm','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'AN','name'=>'DHL Next Day 3:00 pm','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'AS','name'=>'DHL Second Day','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'AG','name'=>'DHL Ground','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'AIE','name'=>'DHL International Express','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'ASTD','name'=>'DHL @Home Standard (Obsolete)','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'ADFR','name'=>'DHL @Home Deferred (Obsolete)','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL01','name'=>'DHL SmartMail Parcel Plus Expedited','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL02','name'=>'DHL SmartMail Parcel Plus Ground','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL03','name'=>'DHL SmartMail BPM Expedited','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL04','name'=>'DHL SmartMail BPM Ground','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL05','name'=>'DHL SmartMail Marketing Parcel Expedited','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL06','name'=>'DHL SmartMail Marketing Parcel Ground','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL07','name'=>'DHL Media Mail Ground Domestic','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL08','name'=>'DHL SmartMail Parcels Expedited','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL09','name'=>'DHL SmartMail Parcels Ground','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL10','name'=>'Priority Mail','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL11','name'=>'First Class Flats','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL12','name'=>'First Class Parcel','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL13','name'=>'DHL GM Business IPA','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL14','name'=>'DHL GM Business ISAL','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL15','name'=>'DHL GM Packet Plus Priority','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL16','name'=>'DHL GM Packet Priority','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL17','name'=>'DHL GM Packet Standard','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL18','name'=>'DHL GM Packet IPA','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL19','name'=>'DHL GM Packet ISAL','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL20','name'=>'Consolidator International','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLP1','name'=>'DHL Paket bis 1 kg','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLP2','name'=>'DHL Paket bis 2 kg','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLP5','name'=>'DHL Paket bis 5 kg','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLP10','name'=>'DHL Paket bis 10 kg','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPC2','name'=>'DHL Parcel bis 2 kg','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLEEN5','name'=>'ExpressEasy National bis 5 k','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLEEN10','name'=>'ExpressEasy National bis 10 kg','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLEEN20','name'=>'ExpressEasy National bis 20 kg','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLEEN31','name'=>'ExpressEasy National bis 31,5 kg','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLEEN1','name'=>'ExpressEasy National bis 1 kg','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLEEN2','name'=>'ExpressEasy National bis 2 kg','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHL21','name'=>'Commercial ePacket','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPC1','name'=>'DHL Parcel bis 1 kg','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLEEN500','name'=>'ExpressEasy National bis 500 g','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLGMP','name'=>'DHL SmartMail Parcel Expedited Max','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLGMPS','name'=>'DHL Parcel International Standard','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLGMDEP','name'=>'DHL Parcel International Expedited (DDP)','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLGMDEU','name'=>'DHL Parcel International Expedited','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLJL','name'=>'DHL Jetline','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLSL','name'=>'DHL Sprintline','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLEEY','name'=>'DHL Express Easy','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLEPK','name'=>'DHL Europack','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLBBE','name'=>'DHL Breakbulk Express','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLME','name'=>'DHL Medical Express','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLEWW','name'=>'DHL Express Worldwide','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLFWW','name'=>'DHL Freight Worldwide','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLES','name'=>'DHL EconomySelect','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLJB','name'=>'DHL Jumbo Box','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLE900','name'=>'DHL Express 9:00','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLE1030','name'=>'DHL Express 10:30','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLE1200','name'=>'DHL Express 12:00','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLGMB','name'=>'DHL Globalmail Business','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLSD','name'=>'DHL Same Day','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLFED','name'=>'DHL SmartMail Flats Expedited','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLGED','name'=>'DHL SmartMail Flats Ground','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLCPL','name'=>'DHL GM Business Canada Post Lettermail','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLCPA','name'=>'DHL GM Direct Canada Post Admail','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLCPS','name'=>'DHL GM Parcel Canada Parcel Standard','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLWBP','name'=>'DHL Workshare GM Business Priority','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLWBS','name'=>'DHL Workshare GM Business Standard','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPCP','name'=>'DHL GM Publication Canada Publication','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPPI','name'=>'DHL GM Publication Priority','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPSI','name'=>'DHL GM Publication Standard','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPRL','name'=>'DHL SM Parcel Return Light','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPRP','name'=>'DHL SM Parcel Return Plus','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPRG','name'=>'DHL SM Parcel Return Ground','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPIDP','name'=>'DHL Parcel International Direct Priority','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPIDS','name'=>'DHL Parcel International Direct Standard','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPIBB','name'=>'DHL Parcel International Breakbulk','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLMNDE','name'=>'DHL Metro Next Day Evening','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLMNDA','name'=>'DHL Metro Next Day Afternoon','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLMND','name'=>'DHL Metro Next Day','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLMSDE','name'=>'DHL Metro Same Day Evening','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLMSDA','name'=>'DHL Metro Same Day Afternoon','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLMSD','name'=>'DHL Metro Same Day','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLEXXD','name'=>'DHL Express Worldwide Document','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLE1200D','name'=>'DHL Express 12:00 Document','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPDIP','name'=>'DHL Parcel Direct Inbound Priority','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPDIS','name'=>'DHL Parcel Direct Inbound Standard','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPDIPF','name'=>'DHL Parcel Direct Inbound Priority Formal','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPDISF','name'=>'DHL Parcel Direct Inbound Priority Forma','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLP','name'=>'DHL Paket','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPSD','name'=>'DHL Paket Taggleich','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLCSD','name'=>'DHL Kurier Taggleich','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLCTIME','name'=>'DHL Kurier Wunschzeit','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPI','name'=>'DHL Paket International','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPCONNECT','name'=>'DHL Paket Connect','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPR','name'=>'DHL Paket Return','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DHLPCONNECTR','name'=>'DHL Paket Connect Return','shipping_carriers_id'=>$dhlCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $uspsCarrier = ShippingCarriers::Create([
            'name' => 'USPS'
        ]);

        $uspsCarrierMethods = array(
            array('value'=>'U01','name'=>'USPS First Class','shipping_carriers_id'=>$uspsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'U02','name'=>'USPS Priority','shipping_carriers_id'=>$uspsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'U03','name'=>'USPS Media Mail','shipping_carriers_id'=>$uspsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'U04','name'=>'USPS Retail Ground','shipping_carriers_id'=>$uspsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'U05','name'=>'USPS Express','shipping_carriers_id'=>$uspsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'U06','name'=>'USPS Bound Printed Matter','shipping_carriers_id'=>$uspsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'U07','name'=>'USPS Library Mail','shipping_carriers_id'=>$uspsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'UI05','name'=>'USPS Intl Express','shipping_carriers_id'=>$uspsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'UI02','name'=>'USPS Intl Priority','shipping_carriers_id'=>$uspsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'UI01','name'=>'USPS Intl First Class','shipping_carriers_id'=>$uspsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'U08','name'=>'USPS Parcel Select','shipping_carriers_id'=>$uspsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'EWS1','name'=>'USPS Critical Mail (Obsolete)','shipping_carriers_id'=>$uspsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'UAS','name'=>'USPS Autoselected','shipping_carriers_id'=>$uspsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $shiprushCarrier = ShippingCarriers::Create([
            'name' => 'ShipRush'
        ]);

        $shiprushCarrierMethods = array(
            array('value'=>'PBCP','name'=>'ShipRush Global','shipping_carriers_id'=>$shiprushCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'SRGLOBAL','name'=>'ShipRush Global ROUTING','shipping_carriers_id'=>$shiprushCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'','name'=>'','shipping_carriers_id'=>$shiprushCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $directLinkCarrier = ShippingCarriers::Create([
            'name' => 'Direct Link'
        ]);

        $directLinkCarrierMethods = array(
            array('value'=>'DLR','name'=>'Direct Link Registered Mail','shipping_carriers_id'=>$directLinkCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DLP','name'=>'Direct Link Parcel','shipping_carriers_id'=>$directLinkCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DLM','name'=>'Direct Link International Mail','shipping_carriers_id'=>$directLinkCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DLBME','name'=>'Business Mail Economy','shipping_carriers_id'=>$directLinkCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DLMMPL1','name'=>'MDSE Mail Plus Level 1','shipping_carriers_id'=>$directLinkCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DLMMPL2','name'=>'MDSE Mail Plus Level 2','shipping_carriers_id'=>$directLinkCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DLMMPL3','name'=>'MDSE Mail Plus Level 3','shipping_carriers_id'=>$directLinkCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DLMMPE','name'=>'MDSE Mail Plus Express','shipping_carriers_id'=>$directLinkCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );


        $deutschePostCarrier = ShippingCarriers::Create([
            'name' => 'Deutsche Post'
        ]);

        $deutschePostCarrierMethods = array(
            array('value'=>'DPPRG','name'=>'Büchersendung Groß','shipping_carriers_id'=>$deutschePostCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DPPRM','name'=>'Büchersendung Maxi','shipping_carriers_id'=>$deutschePostCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DPPG','name'=>'Warensendung Groß','shipping_carriers_id'=>$deutschePostCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DPPK','name'=>'Warensendung Kompakt','shipping_carriers_id'=>$deutschePostCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DPG','name'=>'Großbrief','shipping_carriers_id'=>$deutschePostCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'DPK','name'=>'Kompaktbrief','shipping_carriers_id'=>$deutschePostCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $royalMailCarrier = ShippingCarriers::Create([
            'name' => 'Royal Mail'
        ]);

        $royalMailCarrierMethods = array(
            array('value'=>'RMFC','name'=>'Royal Mail 1st Class','shipping_carriers_id'=>$royalMailCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RMSC','name'=>'Royal Mail 2nd Class','shipping_carriers_id'=>$royalMailCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RMSFC','name'=>'Royal Mail Signed For 1st Class','shipping_carriers_id'=>$royalMailCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RMSSC','name'=>'Royal Mail Signed For 2nd Class','shipping_carriers_id'=>$royalMailCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RMSDG1PM','name'=>'Royal Mail Special Delivery Guaranteed by 1pm','shipping_carriers_id'=>$royalMailCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RMSDG9AM','name'=>'Royal Mail Special Delivery Guaranteed by 9am','shipping_carriers_id'=>$royalMailCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );


        $dynamexCarrier = ShippingCarriers::Create([
            'name' => 'DYNAMEX'
        ]);

        $dynamexCarrierMethods = array(
            array('value'=>'DYNAMEXSD','name'=>'DYNAMEX Same Day','shipping_carriers_id'=>$dynamexCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $delivCarrier = ShippingCarriers::Create([
            'name' => 'Deliv'
        ]);

        $delivCarrierMethods = array(
            array('value'=>'DELIVSAMEDAYSTD','name'=>'Deliv Same Day','shipping_carriers_id'=>$delivCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $onTracCarrier = ShippingCarriers::Create([
            'name' => 'OnTrac'
        ]);

        $onTracCarrierMethods = array(
            array('value'=>'OTSUNRISE','name'=>'OnTrac Sunrise','shipping_carriers_id'=>$onTracCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'OTSUNRISEGOLD','name'=>'OnTrac Sunrise Gold','shipping_carriers_id'=>$onTracCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'OTPALETTIZEDFREIGHT','name'=>'OnTrac Palletized Freight','shipping_carriers_id'=>$onTracCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'OTGROUND','name'=>'OnTrac Ground','shipping_carriers_id'=>$onTracCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'OTSAMEDAY','name'=>'OnTrac Same Day','shipping_carriers_id'=>$onTracCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'OTPARCELSELECT','name'=>'OnTrac Parcel Select','shipping_carriers_id'=>$onTracCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'OTFIRSTCLASS','name'=>'OnTrac First Class','shipping_carriers_id'=>$onTracCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'OTPRIORITY','name'=>'OnTrac Priority','shipping_carriers_id'=>$onTracCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'OTBPM','name'=>'OnTrac Bound Printed Matter','shipping_carriers_id'=>$onTracCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'OTMEDIA','name'=>'OnTrac Media Mail','shipping_carriers_id'=>$onTracCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'OTPARCELSELECTLW','name'=>'OnTrac Parcel Select Lightweight','shipping_carriers_id'=>$onTracCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $asendiaCarrier = ShippingCarriers::Create([
            'name' => 'Asendia'
        ]);

        $asendiaCarrierMethods = array(
            array('value'=>'ASPMI','name'=>'Asendia PMI','shipping_carriers_id'=>$asendiaCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'ASPMEI','name'=>'Asendia PMEI','shipping_carriers_id'=>$asendiaCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'ASPT','name'=>'Asendia Priority Tracked','shipping_carriers_id'=>$asendiaCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'ASIE','name'=>'Asendia International Express','shipping_carriers_id'=>$asendiaCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'ASOT','name'=>'Asendia Other','shipping_carriers_id'=>$asendiaCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'ASEP','name'=>'Asendia ePacket','shipping_carriers_id'=>$asendiaCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'ASIPA','name'=>'Asendia IPA','shipping_carriers_id'=>$asendiaCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'ASISAL','name'=>'Asendia ISAL','shipping_carriers_id'=>$asendiaCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $apcCarrier = ShippingCarriers::Create([
            'name' => 'APC'
        ]);

        $apcCarrierMethods = array(
            array('value'=>'APCBS','name'=>'Book Service','shipping_carriers_id'=>$apcCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'APCEDDP','name'=>'Expedited DDP','shipping_carriers_id'=>$apcCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'APCEDDU','name'=>'Expedited DDU','shipping_carriers_id'=>$apcCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'APCPDDP','name'=>'Priority DDP','shipping_carriers_id'=>$apcCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'APCPDDPD','name'=>'Priority DDP Delcon','shipping_carriers_id'=>$apcCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'APCPDDU','name'=>'Priority DDU','shipping_carriers_id'=>$apcCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'APCPDDUD','name'=>'Priority DDU Delcon','shipping_carriers_id'=>$apcCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'APCPDDUPQW','name'=>'Priority DDU PQW','shipping_carriers_id'=>$apcCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'APCSDDU','name'=>'Standard DDU','shipping_carriers_id'=>$apcCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'APCSDDUPQW','name'=>'Standard DDU PQW','shipping_carriers_id'=>$apcCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'APCPDDU','name'=>'Packet DDU','shipping_carriers_id'=>$apcCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $globeLogisticsCarrier = ShippingCarriers::Create([
            'name' => 'Globegistics'
        ]);

        $globeLogisticsCarrierMethods = array(
            array('value'=>'GGPMEI','name'=>'Globegistics PMEI','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGPMI','name'=>'Globegistics PMI','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGECD','name'=>'Globegistics eCom Domestic','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGECDBPM','name'=>'Globegistics eCom Domestic BPM','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGECDF','name'=>'Globegistics eCom Domestic Flats','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGECEU','name'=>'Globegistics eCom Europe','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGECEXP','name'=>'Globegistics eCom Express','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGECEXT','name'=>'Globegistics eCom Extra','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGECIPA','name'=>'Globegistics eCom IPA','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGECISAL','name'=>'Globegistics eCom ISAL','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGECPACK','name'=>'Globegistics eCom Packet','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGECPRI','name'=>'Globegistics eCom Priority','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGECSTD','name'=>'Globegistics eCom Standard','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGECTDDP','name'=>'Globegistics eCom Tracked DDP','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'GGECTDDU','name'=>'Globegistics eCom Tracked DDU','shipping_carriers_id'=>$globeLogisticsCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );
        
        $rrdCarrier = ShippingCarriers::Create([
            'name' => 'RRD Courier Service'
        ]);

        $rrdCarrierMethods = array(
            array('value'=>'RRDCSDDP','name'=>'RRD Courier Service DDP','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDCSDDU','name'=>'RRD Courier Service DDU','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDDEP','name'=>'RRD Domestic Economy Parcel','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDDPBPM','name'=>'RRD Domestic Parcel BPM','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDDPP','name'=>'RRD Domestic Priority Parcel','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDDPPBPM','name'=>'RRD Domestic Priority Parcel BPM','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDEMI','name'=>'RRD EMI Service','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDEP','name'=>'RRD Economy Parcel Service','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDIPA','name'=>'RRD IPA Service','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRSISAL','name'=>'RRD ISAL Service','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDPMI','name'=>'RRD PMI Service','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDPPDDP','name'=>'RRD Priority Parcel DDP','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDPPDDU','name'=>'RRD Priority Parcel DDU','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDPPDCDDP','name'=>'RRD Priority Parcel Delivery Confirmation DDP','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDPPDCDDU','name'=>'RRD Priority Parcel Delivery Confirmation DDU','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'RRDEPACK','name'=>'RRD ePacket Service','shipping_carriers_id'=>$rrdCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
         );

        $amazonCarrier = ShippingCarriers::Create([
            'name' => 'Amazon'
        ]);

        $amazonCarrierMethods = array(
            array('value'=>'AMZPR','name'=>'Amazon Priority','shipping_carriers_id'=>$amazonCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'AMZSD','name'=>'Amazon Scheduled Delivery','shipping_carriers_id'=>$amazonCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $firstMileCarrier = ShippingCarriers::Create([
            'name' => 'FirstMile'
        ]);

        $firstMileCarrierMethods = array(
            array('value'=>'FMXPE','name'=>'XParcel Expedited','shipping_carriers_id'=>$firstMileCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FMXPR','name'=>'XParcel Returns','shipping_carriers_id'=>$firstMileCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FMXPM','name'=>'XParcel Max','shipping_carriers_id'=>$firstMileCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FMSD','name'=>'FirstMile Same Day','shipping_carriers_id'=>$firstMileCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FMPS','name'=>'FirstMile Parcel Select','shipping_carriers_id'=>$firstMileCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FMPSL','name'=>'FirstMile Parcel Select Lightweight','shipping_carriers_id'=>$firstMileCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'FMIXP','name'=>'FirstMile International XParcel','shipping_carriers_id'=>$firstMileCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );


        $xpoCarrier = ShippingCarriers::Create([
            'name' => 'XPO'
        ]);

        $xpoCarrierMethods = array(
            array('value'=>'XPOPP','name'=>'XPO Priority Parcel','shipping_carriers_id'=>$xpoCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'XPOIPA','name'=>'XPO IPA','shipping_carriers_id'=>$xpoCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'XPOC','name'=>'XPO Courier Service','shipping_carriers_id'=>$xpoCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'XPOEP','name'=>'XPO Economy Parcel','shipping_carriers_id'=>$xpoCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'XPOPC','name'=>'XPO Priority Parcel Courier','shipping_carriers_id'=>$xpoCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'XPOPM','name'=>'XPO Priority Mail','shipping_carriers_id'=>$xpoCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $newgisticCarrier = ShippingCarriers::Create([
            'name' => 'Newgistics'
        ]);

        $newgisticCarrierMethods = array(
            array('value'=>'NGPARCELSELECT','name'=>'Newgistics Parcel Select','shipping_carriers_id'=>$newgisticCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'NGPARCELSELECTLW','name'=>'Newgistics Parcel Select Lightweight','shipping_carriers_id'=>$newgisticCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $pmodCarrier = ShippingCarriers::Create([
            'name' => 'PMOD'
        ]);

        $pmodCarrierMethods = array(
            array('value'=>'PMODFIRSTCLASS','name'=>'PMOD First Class','shipping_carriers_id'=>$pmodCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'PMODPRIORITY','name'=>'PMOD Priority','shipping_carriers_id'=>$pmodCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'PMODMEDIA','name'=>'PMOD Media Mail','shipping_carriers_id'=>$pmodCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'PMODPARCELSELECT','name'=>'PMOD Parcel Select','shipping_carriers_id'=>$pmodCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'PMODPARCELSELECTLW','name'=>'PMOD Parcel Select Lightweight','shipping_carriers_id'=>$pmodCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
            array('value'=>'PMODMARKETING','name'=>'PMOD Marketing Parcel','shipping_carriers_id'=>$pmodCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        $borderGuruCarrier = ShippingCarriers::Create([
            'name' => 'Border Guru'
        ]);

        $borderGuruCarrierMethods = array(
            array('value'=>'HERMESBG','name'=>'BorderGuru','shipping_carriers_id'=>$borderGuruCarrier->id,'created_at' => Carbon::now(),'updated_at' => Carbon::now()),
        );

        ShippingCarrierMethods::insert($upsMethods);
        ShippingCarrierMethods::insert($fedExMethods);
        ShippingCarrierMethods::insert($dhlCarrierMethods);
        ShippingCarrierMethods::insert($uspsCarrierMethods);
        ShippingCarrierMethods::insert($shiprushCarrierMethods);
        ShippingCarrierMethods::insert($royalMailCarrierMethods);
        ShippingCarrierMethods::insert($directLinkCarrierMethods);
        ShippingCarrierMethods::insert($deutschePostCarrierMethods);
        ShippingCarrierMethods::insert($dynamexCarrierMethods);
        ShippingCarrierMethods::insert($delivCarrierMethods);
        ShippingCarrierMethods::insert($onTracCarrierMethods);
        ShippingCarrierMethods::insert($asendiaCarrierMethods);
        ShippingCarrierMethods::insert($apcCarrierMethods);
        ShippingCarrierMethods::insert($globeLogisticsCarrierMethods);
        ShippingCarrierMethods::insert($rrdCarrierMethods);
        ShippingCarrierMethods::insert($amazonCarrierMethods);
        ShippingCarrierMethods::insert($firstMileCarrierMethods);
        ShippingCarrierMethods::insert($xpoCarrierMethods);
        ShippingCarrierMethods::insert($newgisticCarrierMethods);
        ShippingCarrierMethods::insert($pmodCarrierMethods);
        ShippingCarrierMethods::insert($borderGuruCarrierMethods);
    }
}
