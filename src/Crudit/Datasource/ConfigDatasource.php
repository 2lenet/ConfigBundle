<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Crudit\Datasource;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;

class ConfigDatasource extends AbstractDoctrineDatasource
{
    public function getClassName(): string
    {
        $metadata = $this->entityManager->getClassMetadata(ConfigInterface::class);

        return $metadata->name;
    }
}
