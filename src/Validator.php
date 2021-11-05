<?php

namespace SaaSFormation\Vendor\CommandBus;

interface Validator
{
    public function validate(Command $command): bool;
}
