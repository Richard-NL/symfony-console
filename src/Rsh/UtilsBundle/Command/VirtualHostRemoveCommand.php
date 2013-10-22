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
        $finder = new Finder();
        $finder->files()->in($this->getSitesEnabledDir())->name('*.conf');

        $table = $this->getHelperSet()->get('table');
        $table
            ->setHeaders(array('Option', 'Virtualhost'));

        $fileCount = 0;
        $files = array();
        foreach ($finder as $file) {
            $fileCount +=1;
            $files[$fileCount] = $file;
            $table->addRow(array($fileCount, $file->getFilename()));
        }
        $table->render($output);
        $fileCount = $finder->count();

        $dialog = $this->getHelperSet()->get('dialog');
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
        $password = $dialog->askHiddenResponse($output, 'Root permission required to write file: ');
        $this->writeHostFile(str_replace('.conf', '', $files[$option]->getFilename()), $password);


        $this->removeVirtualHostConfigFiles($password, $files[$option]);
        $output->writeln('you have chosen :' . $option . ' which is file :' . $files[$option]);
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

    /**
     * @param $password
     * @param $file
     */
    private function removeVirtualHostConfigFiles($password, $file)
    {
        shell_exec(
            sprintf(
                'echo %s |sudo unlink %s/%s',
                $password,
                $this->getSitesEnabledDir(),
                $file->getFilename()
            )
        );
        shell_exec(
            sprintf(
                'echo %s |sudo rm -f %s/%s',
                $password,
                $this->getSitesAvailableDir(),
                $file->getFilename()
            )
        );
    }
}
