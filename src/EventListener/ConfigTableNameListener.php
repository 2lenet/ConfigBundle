<?php

namespace Lle\ConfigBundle\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Lle\ConfigBundle\Contracts\ConfigInterface;

/**
 * Names the table of the project's configuration entity "lle_config".
 *
 * The entity is declared in each project with the ConfigTrait, which cannot carry a class attribute:
 * without this listener its table would be named after the class ("config").
 */
class ConfigTableNameListener
{
    public const TABLE_NAME = 'lle_config';

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $metadata = $eventArgs->getClassMetadata();
        if ($metadata->isMappedSuperclass || !is_a($metadata->getName(), ConfigInterface::class, true)) {
            return;
        }

        $metadata->setPrimaryTable(['name' => self::TABLE_NAME]);
    }
}
