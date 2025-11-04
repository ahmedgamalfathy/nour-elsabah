<?php
namespace App\Services\Select;

use App\Models\Client\Client;
use App\Models\Client\ClientEmail;
use App\Models\Client\ClientPhone;
use App\Models\Client\ClientAdrress;


class ClientSelectService{
    public function getClients(){
        $clients = Client::all(['id as value', 'name as label']);
        return $clients;
    }
    public function getClientEmails($clientId){
            $clientEmails = ClientEmail::where('client_id', $clientId)->get(['id as value', 'email as label']);
        return $clientEmails;
    }
    public function getClientPhones($clientId){
        $clientPhones = ClientPhone::where('client_id', $clientId)->get(['id as value', 'phone as label']);
    return $clientPhones;
    }
    public function getClientAddress($clientId){
        $clientPhones = ClientAdrress::where('client_id', $clientId)->get(['id as value', 'address as label']);
    return $clientPhones;
}
}

