<?php

namespace Lle\ConfigBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Contracts\WarmupInterface;
use Lle\ConfigBundle\Repository\AbstractConfigRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AsCommand('lle:config:warmup')]
class WarmupCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        #[AutowireIterator('lle.config.warmup')]
        /** @var iterable<WarmupInterface> */
        private iterable $warmups,
    ) {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var AbstractConfigRepository $configRepository */
        $configRepository = $this->em->getRepository(ConfigInterface::class);

        foreach ($this->warmups as $warmup) {
            $warmup->warmup($configRepository);
        }

        $this->em->flush();

        $io = new SymfonyStyle($input, $output);
        $io->success('Warm-up of configuration done !');

        return self::SUCCESS;
    }
}
