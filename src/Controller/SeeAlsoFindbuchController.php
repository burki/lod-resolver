<?php
// src/Controller/SeeAlsoFindbuch.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;;

/**
 * http://beacon.findbuch.de/ no longer works.
 * 
 * Therefore always return 404
 *
 * @Route("/seealso/findbuch")
 */
class SeeAlsoFindbuchController
extends AbstractController
{
    const FINDBUCH_SEEALSO_URL = 'http://beacon.findbuch.de';

    /**
     * @Route("{path}", requirements={"path": ".+"}, name="findbuch-proxy")
     */
    public function seeAlsoFindbuchAction(Request $request, $path)
    {
        throw $this->createNotFoundException(self::FINDBUCH_SEEALSO_URL . ' no longer works');
    }
}
