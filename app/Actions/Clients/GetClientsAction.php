<?php

namespace App\Actions\Clients;

use App\Models\Client;

class GetClientsAction
{
    public function execute()
    {
        return Client::query()
            ->orderBy('organization_name')
            ->get();
    }
}
