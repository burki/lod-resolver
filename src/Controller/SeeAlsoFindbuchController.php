<?php

// src/Controller/SeeAlsoFindbuchController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * http://beacon.findbuch.de/ no longer works.
 *
 * Therefore always return 404
 */
#[Route('/seealso/findbuch')]
class SeeAlsoFindbuchController extends AbstractController
{
    protected const FINDBUCH_SEEALSO_URL = 'http://beacon.findbuch.de';

    #[Route('{path}', name: 'findbuch-proxy', requirements: ['path' => '.+'])]
    public function seeAlsoFindbuchAction(Request $request, $path)
    {
        throw $this->createNotFoundException(self::FINDBUCH_SEEALSO_URL . ' no longer works');
    }
}
