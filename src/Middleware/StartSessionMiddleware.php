<?php
//We use this instead of the session_start we used.
//To avoid using that function in every call of our application
//We create a middleware that will start the session for us.
namespace Salle\PixSalle\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final class StartSessionMiddleware
{
    public function __invoke(Request $request, RequestHandler $next): Response
    {
        session_start();
        return $next->handle($request);
    }
}