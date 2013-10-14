<?php
namespace Rsh\UtilsBundle\Command;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use RuntimeException;

class VirtualHostCreateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('virtualhost:create')
            ->setDescription('Create virtualhost file')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'what\'s the name of the virtualhost?'
            )
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'what\'s the path to the virtualhost files?'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $webRootPath = $input->getArgument('path');
        $this->createFolder($webRootPath);

        $configFileName = '/etc/apache2/sites-available/' . $name . '.conf';
        $symbolicLink = '/etc/apache2/sites-enabled/' . $name . '.conf';


        // throw exception if there already is a virtual host under that name
        if (file_exists($configFileName)) {
            throw new RuntimeException('Config file already exists use a different name');
        }


        $fileContent = $this->buildConfigFileContent($name, $webRootPath);

        $dialog = $this->getHelperSet()->get('dialog');
        $password = $dialog->askHiddenResponse($output, 'Root permission required to write file: ');

        $this->writeVhostConfigFiles($password, $configFileName, $symbolicLink, $fileContent);
        $this->writeHostFile($name, $password);

        $output->writeln('virtual host ready');
    }

    /**
     * Get the content to place in the vhost config
     * @param $name
     * @param $webRootPath
     * @return string
     */
    private function buildConfigFileContent($name, $webRootPath)
    {
        // if the apache version is lower then 2.4.0
        if (-1 === version_compare ($this->getApacheVersionNumber(), '2.4.0')) {
            $fileContent = $this->getContainer()->get('templating')->render(
                'RshUtilsBundle:Default:apache2.2.0.conf.twig',
                array( 'name' => $name, 'webRootPath' =>$webRootPath)
            );

            return $fileContent;
        }

        $fileContent = $this->getContainer()->get('templating')->render(
            'RshUtilsBundle:Default:apache2.4.0.conf.twig',
            array( 'name' => $name, 'webRootPath' =>$webRootPath)
        );

        return $fileContent;

    }

    /**
     * @param $password
     * @param $configFileName
     * @param $symbolicLink
     * @param $fileContent
     */
    private function writeVhostConfigFiles($password, $configFileName, $symbolicLink, $fileContent)
    {
        shell_exec("echo $password | sudo -S touch $configFileName");
        shell_exec("echo $password | echo 'echo \"$fileContent\" > $configFileName' | sudo -s");
        shell_exec("echo $password | sudo -S ln -s $configFileName $symbolicLink");
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
        $fileContent .= PHP_EOL . '127.0.0.1	' . $name;
        shell_exec("echo {$password} | echo 'echo \"$fileContent\" > $configFileName' | sudo -s");
    }

    /**
     * @return string
     * @throws UnexpectedValueException
     */
    private function getApacheVersionNumber() {
        $shellData = shell_exec("apache2 -v");
        $pattern = '/Apache\/(?P<digit1>\d+).(?P<digit2>\d+).(?P<digit3>\d+)/';


        preg_match($pattern, $shellData, $matches);

        if (!isset($matches['digit1']) || !isset($matches['digit2']) || !isset($matches['digit3'])) {
            throw new UnexpectedValueException('Apache version not found');
        }
        $version = sprintf('%d.%d.%d',
            $matches['digit1'],
            $matches['digit2'],
            $matches['digit3']
        );
        return $version;
    }

    /**
     * create folder
     * @param $path
     */
    private function createFolder($path)
    {
        $folders = explode('/', $path);
        $destinationPath = '';
        foreach($folders as $folder) {
            $destinationPath .= '/' . $folder;

            if (file_exists($destinationPath) && is_dir($destinationPath)) {
                continue;
            }

            try {
                mkdir($destinationPath);
            } catch (RuntimeException $e) {
                echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
            }
        }
    }
}