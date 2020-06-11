<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    /**
     * @return array
     */
    public function registerBundles()
    {
        $bundles = [
            // Symfony
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),

            // JMS
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),

            // Doctrine
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),

            // Sensio
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            // MOPA
            new Mopa\Bundle\BootstrapBundle\MopaBootstrapBundle(),

            // FOS
            new FOS\UserBundle\FOSUserBundle(),

            // KNP
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),

            // Ddeboer Data (for CSV)
            new Ddeboer\DataImportBundle\DdeboerDataImportBundle(),

            // BarcodeBundle
            new Hackzilla\BarcodeBundle\HackzillaBarcodeBundle(),

            // PedroTeixeira
            new PedroTeixeira\Bundle\GridBundle\PedroTeixeiraGridBundle(),

            // Datatables
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Sg\DatatablesBundle\SgDatatablesBundle(),

            // Bundle for Select2
            new Pinano\Select2Bundle\PinanoSelect2Bundle(),

            // ZenstruckFormBundle for autocompletion with Ajax
            new Zenstruck\Bundle\FormBundle\ZenstruckFormBundle(),

            // Natue
            new Natue\Bundle\CoreBundle\NatueCoreBundle(),
            new Natue\Bundle\ShippingBundle\NatueShippingBundle(),
            new Natue\Bundle\StockBundle\NatueStockBundle(),
            new Natue\Bundle\UserBundle\NatueUserBundle(),
            new Natue\Bundle\ZedBundle\NatueZedBundle(),
            new Natue\Bundle\PdfBundle\NatuePdfBundle(),
            new Natue\Bundle\DashboardBundle\NatueDashboardBundle(),
            new Natue\Bundle\InvoiceBundle\NatueInvoiceBundle(),

            // Scn Redis
            new Snc\RedisBundle\SncRedisBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev'])) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        if (in_array($this->getEnvironment(), ['dev', 'circleci', 'test'])) {
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
        }

        return $bundles;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        if (in_array($this->environment, ['dev', 'test'])) {
            return '/tmp/wmsCache/' . $this->environment;
        }

        return parent::getCacheDir();
    }

    /**
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
