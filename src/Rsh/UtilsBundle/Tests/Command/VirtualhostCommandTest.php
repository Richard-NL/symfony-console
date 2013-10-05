<?php
namespace Rsh\UtilsBundle\Command;

class VirtualhostCommandTest extends CommandTestCase
 {
     public function testDefaultDoesNotInstall()
     {
         $client = self::createClient();
         $output = $this->runCommand($client, "virtualhost:create foo /home/tu/foo/fighter'");

     }

}
