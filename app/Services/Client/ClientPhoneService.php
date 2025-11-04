<?php
namespace App\Services\Client;

use App\Enums\IsMain;
use App\Models\Client\ClientPhone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class ClientPhoneService
{

   public function allClientPhones(int $clientId)
   {
     return ClientPhone::where('client_id',$clientId)->get();


   }
   public function editClientPhone(int $id)
   {
      return ClientPhone::find($id);
   }
    public function createClientPhone(array $data)
    {
        $clientPhone = ClientPhone::create([
            'client_id' => $data['clientId'],
            'phone' => $data['phone'],
            'is_main' => IsMain::from($data['isMain'])->value,
            'country_code' => $data['countryCode'] ?? null,
        ]);
        if (IsMain::from($data['isMain'])->value=== 1){
            $clientPhones =ClientPhone::where('client_id', $data['clientId'])->where('id','!=',$clientPhone->id)->where('is_main',1)->pluck('id');
            DB::table('client_phones')
            ->whereIn('id', $clientPhones)
            ->update(['is_main' => 0]);
        }

        return $clientPhone;
    }

    public function updateClientPhone(int $id, array $data)
    {
        $clientPhone = ClientPhone::find($id);

        $clientPhone->update([
            'phone' => $data['phone'],
            'is_main' => IsMain::from($data['isMain'])->value,
            'country_code' => $data['countryCode'] ?? null,
        ]);
        if (IsMain::from($data['isMain'])->value=== 1){
            $clientPhones =ClientPhone::where('client_id', $clientPhone->client_id)->where('id','!=',$clientPhone->id)->where('is_main',1)->pluck('id');
            DB::table('client_phones')
            ->whereIn('id', $clientPhones)
            ->update(['is_main' => 0]);
        }
        return $clientPhone;
    }

    public function deleteClientPhone(int $id)
    {
        $clientPhone = ClientPhone::find($id);
        $clientPhone->delete();
    }

}
