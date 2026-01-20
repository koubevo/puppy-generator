<?php

namespace App\Contracts;

interface MessageTransport
{
    public function send(array $payload): bool;
}
