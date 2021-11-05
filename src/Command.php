<?php

namespace SaaSFormation\Vendor\CommandBus;

use StraTDeS\VO\Single\Id;

interface Command
{
    public function id(): Id;

    public function code(): string;

    public function version(): int;

    public function toArray(): array;
}
