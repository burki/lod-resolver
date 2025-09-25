<?php

// src/Controller/SeeAlsoOpenDtBioController.php

namespace App\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Build see also from sameAs links from OpenDtBio
 * See
 *  https://data.deutsche-biographie.de/beta/beacon-open/.
 *
 * TODO: filter out legacy services in
 *  https://data.deutsche-biographie.de/rest/bd/gnd/alle_de
 * such as
 *  jdg / enznz / katdka
 */
#[Route('/seealso/opendtbio')]
class SeeAlsoOpenDtBioController extends SeeAlsoBaseController
{
    protected const OPENDTBIO_URL = 'https://data.deutsche-biographie.de/rest/bd/gnd/%s/alle_de';

    #[Route('/gnd', name: 'opendtbio-gnd')]
    public function seeAlsoGndAction(Request $request): Response
    {
        $id = $request->query->get('id');

        if (empty($id)) {
            throw new BadRequestHttpException('You have to provide a (GND) id');
        }

        $url = sprintf(self::OPENDTBIO_URL, $id);

        $clientResponse = $this->client->request('GET', $url);

        // Responses are lazy: this code is executed as soon as headers are received
        if (200 !== $clientResponse->getStatusCode()) {
            throw new \Exception($url . ' could not be fetched');
        }

        $content = $clientResponse->getContent();

        if (!preg_match('/\s*<\?xml/', $content)) {
            $content = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>' . "\n"
                . $content;
        }

        $crawler = new Crawler($content);

        $seeAlsoId = 'https://d-nb.info/gnd/' . $id;
        $seeAlsoNames = $seeAlsoDescriptions = $seeAlsoUrls = [];

        $deutscheBiographieBase = 'https://www.deutsche-biographie.de/pnd' . $id . '.html';

        foreach ($crawler->filterXPath('//TEI/list/item/list/item/ref') as $domElement) {
            if (!$domElement instanceof \DOMElement) {
                continue;
            }

            $target = $domElement->getAttribute('target');
            if ('#' == $target[0]) {
                $target = $deutscheBiographieBase . $target;
            }

            $seeAlsoUrls[] = $target;
            $seeAlsoNames[] = $domElement->textContent;
            $seeAlsoDescriptions[] = $domElement->textContent;
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
