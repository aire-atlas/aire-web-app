<?php

require_once __DIR__.'/autoload.php';

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
//use Symfony\Bundle\ZendBundle\ZendBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
//use Symfony\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\DoctrineMongoDBBundle\DoctrineMongoDBBundle;
use Zig\Bundle\ZigBundle\ZigBundle;
use Zig\Bundle\PgLinkBundle\PgLinkBundle;
use Ploomap\Bundle\PloomapBundle\PloomapBundle;
use CarnetsBundle\CarnetsBundle;

class AppKernel extends Kernel
{
  /**
   * Overloaded, only to provide an app name
   */
  public function __construct($environment, $debug)
  {
    parent::__construct($environment, $debug);
    $this->name = 'catapatate';
  }

  public function registerRootDir()
  {
    return __DIR__;
  }

  public function registerBundles()
  {
    $bundles = array(
                     // essential bundles
                     new FrameworkBundle(),
                     new TwigBundle(),

                     // third-party : Symfony-related
                     //new ZendBundle(),
                     new SwiftmailerBundle(),
                     //new DoctrineBundle(),
                     //new DoctrineMigrationsBundle(),
                     new DoctrineMongoDBBundle(),

                     // third-party : my owns
                     new ZigBundle(),
                     new PgLinkBundle(),
                     new PloomapBundle(),
                     new CarnetsBundle(),
                     );
    if ($this->isDebug()) {
      $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
    }

    return $bundles;
  }

  public function registerBundleDirs()
  {
    return array
      ('CarnetsBundle' => __DIR__.'/../lib/carnets/server',
       'Symfony\\Bundle' => __DIR__.'/../lib/symfony/src/Symfony/Bundle',
       'Zig\\Bundle' => __DIR__.'/../lib/zig/server/Zig/Bundle',
       'Ploomap\\Bundle' => __DIR__.'/../lib/ploomap/server/Ploomap/Bundle');
  }

  public function registerContainerConfiguration(LoaderInterface $loader)
  {
    $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
  }

}