<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function federationId(): int
    {
        return 1; // TODO: auth()->user()->id_savez kad bude prijava
    }
}
