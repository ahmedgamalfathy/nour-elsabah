<?php
namespace App\Services\Client;
use App\Enums\IsMain;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Models\Client\Client;
use App\Filters\Client\FilterClient;
use App\Helpers\ApiResponse;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClientService
{
    protected $clientService;
    protected $clientPhoneService;
    protected $clientEmailService;
    protected $clientAddressService;
   public function __construct(  ClientPhoneService $clientPhoneService, ClientEmailService $clientEmailService, ClientAddressService $clientAddressService)
    {
        $this->clientPhoneService = $clientPhoneService;
        $this->clientEmailService = $clientEmailService;
        $this->clientAddressService = $clientAddressService;
    }
    public function allClients()
    {
        $perPage = request()->get('pageSize', 10);
        $clients = QueryBuilder::for(Client::class)
        ->allowedSorts('created_at')
        ->allowedFilters([
        AllowedFilter::custom('search', new FilterClient()),
        ])->whereNot('type',1)
        ->orderBy('created_at', 'desc')
        ->paginate($perPage); // Pagination applied here
        return $clients;
    }
    public function editClient(int $id)
    {
        return Client::with(['emails', 'phones', 'addresses'])->find($id);
    }
    public function createClient(array $data): Client
    {
        $client=Client::create([
            'name'=>$data['name'],
            'note'=>$data['note'],
        ]);
      if (isset($data['phones'])) {
        foreach ($data['phones'] as $phone) {
            $this->clientPhoneService->createClientPhone(['clientId'=>$client->id, ...$phone]);
        }
    }

    if (isset($data['addresses'])) {
        foreach ($data['addresses'] as $address) {
            $this->clientAddressService->createClientAddress([
                'clientId'=> $client->id,
                "address"=>$address['address'],
                "isMain"=>$address['isMain'],
                "streetNumber"=>$address['streetNumber']??null,
                "city"=>$address['city']??null,
                "region"=>$address['region']??null
             ]);
        }
    }
    if (isset($data['email'])) {
        $this->clientEmailService->createClientEmail([
        'clientId'=>$client->id,
        'email'=>$data['email'],
        'isMain'=>IsMain::PRIMARY->value ]);
     }
      return $client;
    }
    public function updateClient(int $id, array $data )
    {
        $client = Client::find($id);
        $client->update([
            'name'=>$data['name'],
            'note'=>$data['note'],
        ]);
        return $client;
    }
    public function deleteClient(int $id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
    }
    public function restoreClient($id)
    {
        $client = Client::withTrashed()->findOrFail($id);
        $client->restore();
    }

    public function forceDeleteClient($id)
    {
        $client = Client::withTrashed()->findOrFail($id);
        $client->forceDelete();
    }

}
