<?php

namespace App\Contracts;

interface ContentProvider
{
    public function getPayload(): array;
}
