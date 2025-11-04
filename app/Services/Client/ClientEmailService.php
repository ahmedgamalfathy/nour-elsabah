<?php
namespace App\Services\Client;

use App\Enums\IsMain;
use App\Models\Client\ClientEmail;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class ClientEmailService {
    public function allClientEmails(int $clientId)
    {
        return ClientEmail::where('client_id', $clientId)->get();
    }

    public function createClientEmail(array $data)
    {
        $ClientEmail= ClientEmail::create([
            'client_id' => $data['clientId'],
            'email' => $data['email'],
            'is_main' =>IsMain::from($data['isMain'])->value ,
        ]);
        if($data['isMain']== 1){
            ClientEmail::where('client_id',$data['clientId'])->where('id','!=',$ClientEmail->id)->update([
             'is_main'=>  0
            ]);
        }
        return $ClientEmail;
    }

    public function editClientEmail(int $id)
    {
        $clientEmail = ClientEmail::find($id);
        if(!$clientEmail){
           throw new ModelNotFoundException();
        }
        return $clientEmail;
    }
    public function updateClientEmail(int $id,array $data)
    {
        $ClientEmail=ClientEmail::find($id);
        if(!$ClientEmail){
          throw new ModelNotFoundException() ;
        }
        $ClientEmail->update([
            'client_id' => $data['clientId'],
            'email' => $data['email'],
            'is_main' =>IsMain::from($data['isMain'])->value ,
        ]);
        if($data['isMain']== 1){
            ClientEmail::where('client_id',$data['clientId'])->where('id','!=',$ClientEmail->id)->update([
             'is_main'=>  0
            ]);
        }
        return $ClientEmail;
    }
    public function deleteClientEmail(int $clientId)
    {
        $ClientEmail=ClientEmail::find($clientId);
        if(!$ClientEmail){
            throw new ModelNotFoundException();
        }
        $ClientEmail->delete();
    }
}


