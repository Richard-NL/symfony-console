<?php
namespace Rsh\UtilsBundle\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class VirtualHostReadCommand extends AbstractVirtualHostCommand
{
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
        $table = $this->getHelperSet()->get('table');
        $table
            ->setHeaders(array('filepath', 'Virtualhost'));

        foreach ($finder as $filepath => $file) {
            $table->addRow([$filepath, str_replace('.conf', '', $file->getFilename())]);
        }
        $table->render($output);
    }
}
