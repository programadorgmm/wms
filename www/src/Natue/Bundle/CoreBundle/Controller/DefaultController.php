<?php

namespace Natue\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * Default controller
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/language/{language}", defaults={"language" = "pt_BR"}, name="language")
     * @Template()
     *
     * @param string $language Language
     *
     * @return array
     */
    public function languageAction($language)
    {
        $this->get('session')->set('_locale', $language);

        $refererUrl = $this->get('request')->headers->get('referer');

        if ($refererUrl != null) {
            return $this->redirect($refererUrl);
        } else {
            return $this->redirect($this->generateUrl('homepage'));
        }
    }
}
