<?php
// src/Controller/SeeAlsoEntityfactsController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;;

/**
 * Build see also from sameAs links from Entity Facts.
 * See
 */
#[Route('/seealso/entityfacts')]
class SeeAlsoEntityfactsController
extends AbstractController
{
    const ENTTITYFACTS_URL = 'https://hub.culturegraph.org/entityfacts';

    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /* https://www.geekality.net/blog/valid-javascript-identifier */
    private function isValidJavaScriptIdentifier($subject)
    {
        $identifier_syntax
          = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';

        $reserved_words = [
            'break', 'do', 'instanceof', 'typeof', 'case',
            'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue',
            'for', 'switch', 'while', 'debugger', 'function', 'this', 'with',
            'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum',
            'extends', 'super', 'const', 'export', 'import', 'implements', 'let',
            'private', 'public', 'yield', 'interface', 'package', 'protected',
            'static', 'null', 'true', 'false'
        ];

        return preg_match($identifier_syntax, $subject)
            && ! in_array(mb_strtolower($subject, 'UTF-8'), $reserved_words);
    }

    /**
     *
     */
    #[Route('/gnd', name: 'entityfacts')]
    public function seeAlsoGndAction(Request $request): Response
    {
        $id = $request->query->get('id');

        if (empty($id)) {
            throw new BadRequestHttpException('You have to provide a (GND) id');
        }

        $url = self::ENTTITYFACTS_URL  . '/' . $id;

        $clientResponse = $this->client->request('GET', $url);

        // Responses are lazy: this code is executed as soon as headers are received
        if (200 !== $clientResponse->getStatusCode()) {
            throw new \Exception($url . ' could not be fetched');
        }

        $content = $clientResponse->toArray();

        // result is in OpenSearch format:
        // https://web.archive.org/web/20090323004010/http://www.opensearch.org/Specifications/OpenSearch/Extensions/Suggestions/1.0

        $seeAlsoId = $content['@id'];

        $seeAlsoNames = $seeAlsoDescriptions = $seeAlsoUrls = [];

        if (array_key_exists('sameAs', $content)) {
            foreach ($content['sameAs'] as $sameAs) {
                $seeAlsoUrls[] = $sameAs['@id'];
                $seeAlsoNames[] = $sameAs['collection']['name'];
                $seeAlsoDescriptions[] = $sameAs['collection']['publisher'];
            }
        }

        $result = [
            $seeAlsoId,
            $seeAlsoNames,
            $seeAlsoDescriptions,
            $seeAlsoUrls,
        ];

        // https://github.com/gbv/seealso/blob/master/htdocs/seealso.js
        // expects JSONP by default to work around same-origin
        $callback = $request->query->get('callback');
        if (!empty($callback) && $this->isValidJavaScriptIdentifier($callback)) {
            // return JSONP, see https://en.wikipedia.org/wiki/JSONP
            $scriptCode = sprintf('%s(%s);',
                                  $callback, json_encode($result));
            $response = new Response($scriptCode);
            $response->headers->set('Content-Type', 'text/javascript');

            return $response;
        }

        // return JSON
        return new JsonResponse($result);
    }
}
