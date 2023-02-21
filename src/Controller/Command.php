<?php

namespace App\Controller;

interface Command
{
    public function execute(): void;
}