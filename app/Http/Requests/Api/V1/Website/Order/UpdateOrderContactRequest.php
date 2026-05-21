<?php

namespace App\Http\Requests\Api\V1\Website\Order;

use App\Http\Requests\Api\V1\BaseApiFormRequest;

class UpdateOrderContactRequest extends BaseApiFormRequest
{
    public function rules(): array
    {
        return [
            'client.clientPhoneId' => ['required', 'exists:client_phones,id'],
            'client.clientEmailId' => ['required', 'exists:client_emails,id'],
            'client.clientAddressId' => ['required', 'exists:client_addresses,id'],
        ];
    }
}
