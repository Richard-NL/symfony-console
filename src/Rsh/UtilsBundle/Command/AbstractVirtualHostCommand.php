<?php
namespace Rsh\UtilsBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class AbstractVirtualHostCommand extends ContainerAwareCommand
{
    protected $sitesEnabledDir = '/etc/apache2/sites-enabled';

    public function setSitesEnabledDir($sitesEnabledDir)
    {
        $this->sitesEnabledDir = $sitesEnabledDir;
    }

    public function getSitesEnabledDir()
    {
        return $this->sitesEnabledDir;
    }
}
