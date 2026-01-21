<?php

namespace App\Contracts;

interface ContentProvider
{
    public function getPayload(): array;

    public function getProviderName(): string;
}
