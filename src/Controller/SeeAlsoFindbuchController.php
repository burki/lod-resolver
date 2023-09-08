<?php
// src/Controller/SeeAlsoFindbuch.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;;

/**
 * Simple pass-through proxy since https://beacon.findbuch.de/ has an
 * expired certificate
 *
 * @Route("/seealso/findbuch")
 */
class SeeAlsoFindbuchController
extends AbstractController
{
    const FINDBUCH_SEEALSO_URL = 'http://beacon.findbuch.de';

    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("{path}", requirements={"path": ".+"}, name="findbuch-proxy")
     */
    public function seeAlsoFindbuchAction(Request $request, $path)
    {
        $url = self::FINDBUCH_SEEALSO_URL . $path;

        $queryString = http_build_query($request->query->all());
        if (!empty($queryString)) {
            $url .= '?' . $queryString;
        }

        $clientResponse = $this->client->request('GET', $url);

        // Responses are lazy: this code is executed as soon as headers are received
        if (200 !== $clientResponse->getStatusCode()) {
            throw new \Exception($url . ' could not be fetched');
        }

        $response = new StreamedResponse();

        $contentType = $clientResponse->getHeaders()['content-type'][0];
        $response->headers->set('Content-Type', $contentType);

        $response->setCallback(function () use ($clientResponse) {
            foreach ($this->client->stream($clientResponse) as $chunk) {
                echo $chunk->getContent();
                flush();
            }
        });

        return $response;
    }
}
