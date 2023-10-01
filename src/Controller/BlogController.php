<?php
declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Salle\PixSalle\Repository\BlogRepository;
use Salle\PixSalle\Repository\PortfolioRepository;
use Salle\PixSalle\Repository\UserRepository;
use Slim\Views\Twig;


final class BlogController {
    private Twig $twig;
    private BlogRepository $blogRepository;
    private UserRepository $userRepository;

    public function __construct(Twig $twig, BlogRepository $blogRepository, UserRepository $userRepository) {
        $this->twig = $twig;
        $this->blogRepository = $blogRepository;
        $this->userRepository = $userRepository;
    }

    public function showBlog(Request $request, Response $response): Response {
        return $this->twig->render(
            $response,
            'blog.twig',
            [
                'email' => $_SESSION['email'],
                'entries' => $this->blogRepository->getBlogEntries()
            ]
        );
    }


    public function showBlogEntry(Request $request, Response $response, array $args): Response {
        $entryId = intval($args['id'] ?? 0);
        return $this->twig->render(
            $response,
            'blog-entry.twig',
            [
                'entry' => $this->blogRepository->getBlogEntry($entryId),
                'email' => $_SESSION['email']
            ]
        );
    }

    public function showCreateBlogEntry(Request $request, Response $response): Response {
        return $this->twig->render(
            $response,
            'create-blog-entry.twig',
            [
                'email' => $_SESSION['email']
            ]
        );
    }



    /**
     * BLOG API METHODS --------------------------------------------------------
     */

    public function getBlogEntries(Request $request, Response $response): Response {
        $entries = $this->blogRepository->getBlogEntries();
        $responseBody = json_encode($entries);

        $response->getBody()->write($responseBody);
        return $response->withHeader('content-type', 'application/json')->withStatus(200);
    }

    public function getBlogEntry(Request $request, Response $response, array $args): Response {
        $entryId = intval($args['id'] ?? 0);
        $responseError = <<<body
        {"message": "Blog entry with id $entryId does not exist"}
        body;
        if($entryId <= 0){
            $response->getBody()->write($responseError);
            return $response->withHeader('content-type', 'application/json')->withStatus(404);
        }

        //If everything is correct, retrieve the blog entry
        $entry = $this->blogRepository->getBlogEntry($entryId);
        if($entry == null) {
            $response->getBody()->write($responseError);
            return $response->withHeader('content-type', 'application/json')->withStatus(404);
        }

        //If the entry is not null, send it
        $response->getBody()->write(json_encode($entry));
        return $response->withHeader('content-type', 'application/json')->withStatus(200);
    }

    public function postBlogEntry(Request $request, Response $response): Response {
        $parsedBody = $request->getParsedBody();
        if(isset($parsedBody['title']) && isset($parsedBody['content']) && isset($parsedBody['userId'])){
            $title = $parsedBody['title'];
            $content = $parsedBody['content'];
            $userId = $parsedBody['userId'];

            $blogEntry = $this->blogRepository->createBlogEntry($title, $content, intval($userId));

            $response->getBody()->write(json_encode($blogEntry));
            return $response->withHeader('content-type', 'application/json')->withStatus(201);
        }

        //If something is not in the POST, return error
        $responseBody = <<<body
        {"message": "'title' and/or 'content' and/or 'userId' key missing"}
        body;
        $response->getBody()->write($responseBody);
        return $response->withHeader('content-type', 'application/json')->withStatus(400);
    }

    public function putBlogEntry(Request $request, Response $response, array $args): Response {
        $parsedBody = $request->getParsedBody();
        $entryId = intval($args['id'] ?? 0);
        if(isset($parsedBody['title']) && isset($parsedBody['content']) && !$entryId <= 0){
            $title = $parsedBody['title'];
            $content = $parsedBody['content'];

            $blogEntry = $this->blogRepository->modifyBlogEntry($entryId, $title, $content);

            //If the blog entry doesn't exist, return a 404
            if($blogEntry == null){
                $responseBody = <<<body
                {"message": "Blog entry with id $entryId does not exist"}
                body;
                $response->getBody()->write($responseBody);
                return $response->withHeader('content-type', 'application/json')->withStatus(404);
            }

            //If everything is correct, the entry would have been modified, so return its new contents
            $response->getBody()->write(json_encode($blogEntry));
            return $response->withHeader('content-type', 'application/json')->withStatus(200);
        }

        //If something is not in the PUT, return error
        $responseBody = <<<body
        {"message": "'title' and/or 'content' key missing"}
        body;
        $response->getBody()->write($responseBody);
        return $response->withHeader('content-type', 'application/json')->withStatus(400);
    }

    public function deleteBlogEntry(Request $request, Response $response, array $args): Response {
        $entryId = intval($args['id'] ?? 0);

        if($entryId > 0){
            $success = $this->blogRepository->deleteBlogEntry($entryId);
            if($success){
                $responseBody = <<<body
                {"message": "Blog entry with id $entryId was successfully deleted"}
                body;
                $response->getBody()->write($responseBody);
                return $response->withHeader('content-type', 'application/json')->withStatus(200);
            }
        }

        $errorResponse = <<<body
        {"message": "Blog entry with id $entryId does not exist"}
        body;
        $response->getBody()->write($errorResponse);
        return $response->withHeader('content-type', 'application/json')->withStatus(404);
    }
}
