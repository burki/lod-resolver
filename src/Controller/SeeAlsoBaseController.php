<?php

// src/Controller/SeeAlsoBaseController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Build see also from sameAs links from OpenDtBio
 * See
 *  http://data.deutsche-biographie.de/rest/bd/gnd/alle_de.
 */
abstract class SeeAlsoBaseController extends AbstractController
{
    protected $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /* https://www.geekality.net/blog/valid-javascript-identifier */
    protected function isValidJavaScriptIdentifier(string $subject): bool
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
            'static', 'null', 'true', 'false',
        ];

        return preg_match($identifier_syntax, $subject)
            && !in_array(mb_strtolower($subject, 'UTF-8'), $reserved_words);
    }

    protected function buildJsonResponse(Request $request, array $result): Response
    {
        // https://github.com/gbv/seealso/blob/master/htdocs/seealso.js
        // expects JSONP by default to work around same-origin
        $callback = $request->query->get('callback');
        if (!empty($callback) && $this->isValidJavaScriptIdentifier($callback)) {
            // return JSONP, see https://en.wikipedia.org/wiki/JSONP
            $scriptCode = sprintf(
                '%s(%s);',
                $callback,
                json_encode($result)
            );
            $response = new Response($scriptCode);
            $response->headers->set('Content-Type', 'text/javascript');

            return $response;
        }

        // return JSON
        return new JsonResponse($result);
    }
}
