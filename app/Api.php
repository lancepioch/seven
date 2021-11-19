<?php

namespace App;

use Illuminate\Support\Facades\Http;

class Api
{
    private $defaults;

    public function __construct($user, $token)
    {
        $this->defaults = [
            'raw' => 'true',
            'adminuser' => config('seven.api.user'),
            'admintoken' => config('seven.api.token'),
        ];
    }

    public function execute(string $command): string
    {
        $url = config('seven.api.url') . '/api/executeconsolecommand';
        $response = Http::get($url, $this->defaults + ['command' => $command]);

        return $response->body();
    }
}
