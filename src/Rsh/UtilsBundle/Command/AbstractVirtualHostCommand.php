<?php
namespace Rsh\UtilsBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class AbstractVirtualHostCommand extends ContainerAwareCommand
{
    protected $sitesEnabledDir = '/etc/apache2/sites-enabled';
    protected $sitesAvailableDir = '/etc/apache2/sites-available';

    public function setSitesEnabledDir($sitesEnabledDir)
    {
        $this->sitesEnabledDir = $sitesEnabledDir;
    }

    public function getSitesEnabledDir()
    {
        return $this->sitesEnabledDir;
    }

    public function setSitesAvailableDir($sitesAvailableDir)
    {
        $this->sitesAvailableDir = $sitesAvailableDir;
    }

    public function getSitesAvailableDir()
    {
        return $this->sitesAvailableDir;
    }
}
