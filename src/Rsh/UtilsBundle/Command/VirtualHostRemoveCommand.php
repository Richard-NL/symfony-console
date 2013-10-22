<?php
namespace Rsh\UtilsBundle\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use RunTimeException;
class VirtualHostRemoveCommand extends AbstractVirtualHostCommand
{
    protected function configure()
    {
        $this
            ->setName('virtualhost:remove')
            ->setDescription('remove virtualhost');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        $finder = new Finder();
        $finder->files()->in($this->getSitesEnabledDir())->name('*.conf');


        $table = $this->getHelperSet()->get('table');
        $table
            ->setHeaders(array('Option', 'Virtualhost'));

        $fileCount = 0;
        foreach ($finder as $file) {
            $fileCount +=1;
            $table->addRow(array($fileCount, $file->getFilename()));
        }
        $table->render($output);

        $option = $dialog->askAndValidate(
            $output,
            'which virtualhost would you like to remove?',
            function ($answer) use ($fileCount) {
                $answer = (int)$answer;
                if ($answer < 1 || $answer > $fileCount) {
                    throw new RunTimeException(
                        'Option not available'
                    );
                }
                return $answer;
            }
        );

        $output->writeln('you have chosen :' . $option);
    }

    /**
     * @param $name
     * @param $password
     */
    private function writeHostFile($name, $password)
    {
        $configFileName = '/etc/hosts';
        $lines = file($configFileName, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $key => $line) {
            if (preg_match("/{$name}\$/", $line)) {
                unset($lines[$key]);
            }
        }

        $fileContent = implode(PHP_EOL, $lines);
        shell_exec("echo {$password} | echo 'echo \"$fileContent\" > $configFileName' | sudo -s");
    }
}
