<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttachIconLinkHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set(
            'Link',
            '</icon.png>; rel="icon"; type="image/png"; sizes="128x128", '
            .'</icon.jpeg>; rel="icon"; type="image/jpeg"; sizes="736x736"',
            false,
        );

        return $response;
    }
}
