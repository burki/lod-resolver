<?php

// src/Controller/SeeAlsoEntityfactsController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Build see also from sameAs links from Entity Facts.
 * See
 *  https://www.dnb.de/DE/Professionell/Metadatendienste/Datenbezug/Entity-Facts/entityFacts_node.html.
 */
#[Route('/seealso/entityfacts')]
class SeeAlsoEntityfactsController extends SeeAlsoBaseController
{
    protected const ENTTITYFACTS_URL = 'https://hub.culturegraph.org/entityfacts';

    #[Route('/gnd', name: 'entityfacts-gnd')]
    public function seeAlsoGndAction(Request $request): Response
    {
        $id = $request->query->get('id');

        if (empty($id)) {
            throw new BadRequestHttpException('You have to provide a (GND) id');
        }

        $url = self::ENTTITYFACTS_URL . '/' . $id;

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

        return $this->buildJsonResponse($request, $result);
    }
}
