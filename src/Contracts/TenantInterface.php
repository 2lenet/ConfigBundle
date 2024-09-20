<?php

namespace Lle\ConfigBundle\Contracts;

interface TenantInterface
{
    public function getTenantId(): int;
}