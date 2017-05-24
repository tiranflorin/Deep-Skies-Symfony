<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Lexik\Bundle\FormFilterBundle\LexikFormFilterBundle(),
            new Ob\HighchartsBundle\ObHighchartsBundle(),
            new Braincrafted\Bundle\BootstrapBundle\BraincraftedBootstrapBundle(),
            new Tetranz\Select2EntityBundle\TetranzSelect2EntityBundle(),
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
            new Uran1980\FancyBoxBundle\Uran1980FancyBoxBundle(),
            new Dso\HomeBundle\DsoHomeBundle(),
            new Dso\UserBundle\DsoUserBundle(),
            new Dso\SearchBundle\DsoSearchBundle(),
            new Dso\PlannerBundle\DsoPlannerBundle(),
            new Dso\ObservationsLogBundle\DsoObservationsLogBundle(),
            new Dso\TimelineBundle\DsoTimelineBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new JordiLlonch\Bundle\CrudGeneratorBundle\JordiLlonchCrudGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
