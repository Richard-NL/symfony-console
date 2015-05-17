<?php

namespace Rsh\UtilsBundle\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
class ImageResizeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('images:resize')
            ->setDescription('resizes images from folder')
            ->addArgument(
                'sourceFolder',
                InputArgument::REQUIRED,
                'what\'s the source folder?'
            )
            ->addArgument(
                'destinationFolder',
                InputArgument::REQUIRED,
                'what\'s the destination folder'
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceFolder = $input->getArgument('sourceFolder');
        $destinationFolder = $input->getArgument('destinationFolder');

        $dir = new \DirectoryIterator($sourceFolder);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $output->writeln($fileinfo->getFilename());
                $this->getContainer()->get('image.handling')->open($sourceFolder . '/'. $fileinfo->getFilename())
                    ->resize(250, 250)
                    ->save($destinationFolder . '/'. $fileinfo->getFilename());
            }
        }
    }
} 