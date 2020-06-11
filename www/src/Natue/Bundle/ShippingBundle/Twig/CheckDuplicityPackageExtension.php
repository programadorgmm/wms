<?php 
namespace Natue\Bundle\ShippingBundle\Twig;

use Doctrine\ORM\EntityManager;

class CheckDuplicityPackageExtension extends \Twig_Extension
{

    public function __construct(EntityManager $EntityManager)
    {
        $this->EntityManager = $EntityManager;
    }

    /**
    * @var ShippingVolumeRepository
    */
    protected $shippingVolumeRepository;

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('check_duplicity_package', array($this, 'checkDuplicityPackage')),
        );
    }

    public function checkDuplicityPackage($providerId)
    {
        $ShippingVolumeRepository = $this->EntityManager->getRepository(
            'NatueShippingBundle:ShippingVolume'
        );
        $package = $ShippingVolumeRepository->checkDuplicityPackage($providerId);

        return $package['checkDuplicity'];
    }

    public function getName()
    {
        return 'check_duplicity_package';
    }
}
