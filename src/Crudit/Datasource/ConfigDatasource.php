<?php

declare(strict_types=1);

namespace App\Crudit\Datasource;

use App\Entity\Config;
use Lle\CruditBundle\Datasource\AbstractDoctrineDatasource;

class ConfigDatasource extends AbstractDoctrineDatasource
{
    public function getClassName(): string
    {
        return Config::class;
    }
}
