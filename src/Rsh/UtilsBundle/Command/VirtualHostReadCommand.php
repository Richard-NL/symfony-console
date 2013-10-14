<?php
namespace Rsh\UtilsBundle\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;


class VirtualHostReadCommand extends ContainerAwareCommand
{
    private $sitesEnabledDir = '/etc/apache2/sites-enabled';

    public function setSitesEnabledDir($sitesEnabledDir)
    {
        $this->sitesEnabledDir = $sitesEnabledDir;
    }

    public function getSitesEnabledDir()
    {
        return $this->sitesEnabledDir;
    }

    protected function configure()
    {
        $this
            ->setName('virtualhost:read')
            ->setDescription('read virtualhost files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new Finder();
        $finder->files()->in($this->getSitesEnabledDir())->name('*.conf');

        foreach ($finder as $file) {
            $output->writeln(str_replace('.conf', '', $file->getFilename()));
        }
    }
}
