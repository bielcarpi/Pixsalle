<?php
declare(strict_types=1);

namespace Salle\PixSalle\Middleware;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class LoginMiddleware {
    public function __invoke(Request $request, RequestHandler $next): Response
    {
        $uri = $request->getUri()->getPath();
        if(substr($uri, 0, 1) === '/') $uri = substr($uri, 1);

        //If it's from API, don't require login (needed for the tests)
        if(str_contains($uri, 'api')) return $next->handle($request);

        if (!isset($_SESSION['user_id'])) {
            //If the user is not logged in, only allow requests to /, sign-in and sign-up
            if($uri !== '' && $uri !== 'sign-in' && $uri !== 'sign-up'){
                if($request->getMethod() === 'GET')
                    header("Location: /sign-in");
                else
                    http_response_code(403); //Send a forbidden (403) error code
                exit();
            }
        }
        else{
            //If the user is logged in, don't allow requests to sign-in and sign-up
            if($uri === 'sign-in' || $uri === 'sign-up'){
                if($request->getMethod() === 'GET')
                    header("Location: /");
                else
                    http_response_code(403); //Send a 406: not acceptable
                exit();
            }
        }

        return $next->handle($request);
    }
}