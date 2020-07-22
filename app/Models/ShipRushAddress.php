<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipRushAddress extends Model
{
    public $Address1;
    public $Address2;
    public $FirstName;
    public $LastName;
    public $Company;
    public $City;
    public $State;
    public $PostalCode;
    public $Country;
    public $PhoneNumber;

    public function getFormattedAddress($xml) {
        $xmladdress = $xml->createElement('Address');
        $xmladdress->appendChild($xml->createElement('FirstName', htmlspecialchars($this->FirstName, ENT_XML1, 'UTF-8')));
        $xmladdress->appendChild($xml->createElement('LastName', htmlspecialchars($this->LastName, ENT_XML1, 'UTF-8')));
        $xmladdress->appendChild($xml->createElement('Company', htmlspecialchars($this->Company, ENT_XML1, 'UTF-8')));
        $xmladdress->appendChild($xml->createElement('Address1', htmlspecialchars($this->Address1, ENT_XML1, 'UTF-8')));
        $xmladdress->appendChild($xml->createElement('Address2', htmlspecialchars($this->Address2, ENT_XML1, 'UTF-8')));
        $xmladdress->appendChild($xml->createElement('City', $this->City));
        $xmladdress->appendChild($xml->createElement('State', $this->State));
        $xmladdress->appendChild($xml->createElement('Country', $this->Country));
        $xmladdress->appendChild($xml->createElement('PostalCode', $this->PostalCode));
        $xmladdress->appendChild($xml->createElement('Phone', $this->PhoneNumber));
        return $xmladdress;
    }
}
