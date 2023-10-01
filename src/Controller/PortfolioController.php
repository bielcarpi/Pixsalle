<?php
declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Salle\PixSalle\Model\Album;
use Salle\PixSalle\Model\Portfolio;
use Salle\PixSalle\Repository\PortfolioRepository;
use Salle\PixSalle\Repository\UserRepository;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteContext;
use Slim\Views\Twig;


final class PortfolioController {
    private Twig $twig;
    private UserRepository $userRepository;
    private PortfolioRepository $portfolioRepository;

    public function __construct(
        Twig $twig,
        PortfolioRepository $portfolioRepository,
        UserRepository $userRepository
    ) {
        $this->twig = $twig;
        $this->portfolioRepository = $portfolioRepository;
        $this->userRepository = $userRepository;
    }

    public function showPortfolio(Request $request, Response $response, array $args): Response {
        $portfolioId = $args['id'] ?? null;

        if($portfolioId !== null){
            $portfolio = $this->portfolioRepository->getPortfolio(intval($portfolioId));
            $allowed = false; //The user can't edit the portfolio

            //If the portfolio we're getting is the portfolio of the logged-in user, redirect to /portfolio (without {id})
            if(isset($portfolio) && $portfolio->author() === $_SESSION['email']){
                $routeParser = RouteContext::fromRequest($request)->getRouteParser();
                $response->withStatus(302);
                return $response->withHeader('Location', $routeParser->urlFor('userPortfolio'));
            }
        }
        else{
            //If we're not given a portfolioId it means that we want the user portfolio
            $userId = $this->userRepository->getUserByEmail($_SESSION['email'])->id();
            $portfolio = $this->portfolioRepository->getPortfolio($userId);
            $allowed = $this->userRepository->getUserMembership($_SESSION['email']) === 1; //Only allow to edit it if the user's membership is Active (1)
        }

        if(isset($_SESSION['email'])){
            $imageDir = $this->userRepository->getPicByEmail($_SESSION['email']);
        }else{
            $imageDir = "default.png";
        }

        if(isset($portfolio)){
            $templateArgs = [
                'profilePic' => $imageDir,
                'email' => $_SESSION['email'],
                'author' => $portfolio->author() ?? null,
                'allowed' => $allowed,
                'ownPortfolio' => $portfolio->author() === $_SESSION['email'],
                'title' => $portfolio->title() ?? null,
                'description' => $portfolio->description() ?? null,
                'albums' => $portfolio->albums() ?? null
            ];
        }
        else if(!isset($args['id'])){ //If the argument id does not exist, it means we want our portfolio
            $templateArgs = [
                'profilePic' => $imageDir,
                'email' => $_SESSION['email'],
                'author' => $_SESSION['email'],
                'allowed' => $allowed,
                'ownPortfolio' => true
            ];
        }
        else{
            $templateArgs = [
                'profilePic' => $imageDir,
                'email' => $_SESSION['email']
            ];
        }

        return $this->twig->render(
            $response,
            'portfolio.twig',
            $templateArgs
        );
    }


    public function createPortfolio(Request $request, Response $response): Response {
        //If the user is not allowed to create a portfolio (it is not an Active user), return a 403 (Forbidden)
        if($this->userRepository->getUserMembership($_SESSION['email']) !== 1)
            return $response->withStatus(403);

        //If the user already has a portfolio, return a 403 (Forbidden)
        if($this->portfolioRepository->getPortfolio(intval($_SESSION['user_id'])) !== null)
            return $response->withStatus(403);

        //If the user is allowed to create a portfolio and he hasn't already created one, proceed
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        if(!isset($data['title']) || strlen($data['title']) < 10 || strlen($data['title']) > 40)
            $errors['title'] = 'Error. The title must be between 10 and 40 characters';

        if(!isset($data['description']) || strlen($data['description']) < 50 || strlen($data['description']) > 300)
            $errors['description'] = 'Error. The description must be between 50 and 300 characters';

        //If there are some errors, render them
        if(isset($errors)){
            return $this->twig->render(
                $response,
                'portfolio.twig',
                [
                    'email' => $_SESSION['email'],
                    'allowed' => $this->userRepository->getUserMembership($_SESSION['email']),
                    'ownPortfolio' => true,
                    'formErrors' => $errors,
                    'formData' => $data
                ]
            );
        }


        //If there are no errors, proceed to create the portfolio & refresh the page
        $this->portfolioRepository->createPortfolio($_SESSION['email'], new Portfolio($data['title'], '', $data['description'], null));
        $response->withStatus(302);
        return $response->withHeader('Location', $routeParser->urlFor('userPortfolio'));
        //Using Post/Redirect/Get pattern
    }


    public function createPortfolioAlbum(Request $request, Response $response): Response {
        //If the user is not allowed to create an album (it is not an Active user), return a 403 (Forbidden)
        if($this->userRepository->getUserMembership($_SESSION['email']) !== 1)
            return $response->withStatus(403);

        //If the user doesn't have a portfolio, return a 403 (Forbidden)
        if($this->portfolioRepository->getPortfolio(intval($_SESSION['user_id'])) === null)
            return $response->withStatus(403);

        //If the user is allowed to create an album and its portfolio is created, continue...
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        //Check if the user has enough funds. If not, redirect to the wallet
        if($this->userRepository->getUserFunds($_SESSION['email']) < 2){
            return $response->withStatus(302)->withHeader("Location", $routeParser->urlFor("wallet"));
        }

        if(!isset($data['title']) || strlen($data['title']) < 5 || strlen($data['title']) > 20){
            $response->getBody()->write('Error. The title must be between 5 and 20 characters');
            return $response->withStatus(406);
        }
        else if(!isset($data['cover']) || !filter_var($data['cover'], FILTER_VALIDATE_URL)){
            $response->getBody()->write('Error. The cover provided is not a valid URL');
            return $response->withStatus(406);
        }


        //If everything is correct, return a 200. If we can't create the album, return 500
        $success = $this->portfolioRepository->addAlbumToPortfolio($_SESSION['email'], new Album(-1, -1, $data['title'], [$data['cover']]));
        if(!$success){
            $response->getBody()->write('Internal Error. Please, try again later.');
            return $response->withStatus(500);
        }

        return $response->withStatus(200);
    }


    public function showAlbum(Request $request, Response $response, array $args): Response
    {
        $albumId = intval($args['id']);
        $album = $this->portfolioRepository->getAlbum($albumId);

        if($album !== null){
            $isAlbumFromUser = $album->author() == $_SESSION['email'];
            //If the album is from the user, check if it has the correct membership to edit it
            if($isAlbumFromUser)
                $isAlbumFromUser = $this->userRepository->getUserMembership($_SESSION['email']) === 1;

            $arr =
                [
                    'email' => $_SESSION['email'],
                    'isAlbumFromUser' => $isAlbumFromUser,
                    'album' => $album
                ];
        }
        else{
            $arr =
                [
                    'email' => $_SESSION['email']
                ];
        }

        return $this->twig->render(
            $response,
            'portfolio-album.twig',
            $arr
        );
    }


    public function addPhotoToAlbum(Request $request, Response $response, array $args): Response
    {
        $imageUrl = $request->getParsedBody()['url'] ?? '';
        if(!filter_var($imageUrl, FILTER_VALIDATE_URL)){
            $response->getBody()->write("Sorry, the URL you entered is not valid.");
            return $response->withStatus(406); //Not acceptable
        }

        $status = $this->portfolioRepository->addPhotoToAlbum($_SESSION['email'], intval($args['id']), $imageUrl);
        if($status === 0){
            return $response->withstatus(200); //Everything OK, the photo was correctly added
        }
        else if($status === 1){
            $response->getbody()->write("Sorry, you don't have permissions to add photos to this Album.");
            return $response->withstatus(403); //Forbidden
        }
        else{
            $response->getbody()->write("Internal Error. Please, try again later.");
            return $response->withstatus(500); //Internal Server Error
        }
    }


    public function deletePhotoFromAlbum(Request $request, Response $response, array $args): Response
    {
        $photoToDelete = $request->getParsedBody()['id'] ?? '';
        if(!filter_var($photoToDelete, FILTER_VALIDATE_INT)){
            $response->getBody()->write("Sorry, you haven't entered a correct Photo ID. Try refreshing the webpage.");
            return $response->withStatus(406); //Not acceptable
        }

        $status = $this->portfolioRepository->removePhotoFromAlbum($_SESSION['email'], intval($args['id']), intval($photoToDelete));
        if($status === 0){
            return $response->withstatus(200); //Everything OK, the photo was correctly added
        }
        else if($status === 1){
            $response->getbody()->write("Sorry, you don't have permissions to remove photos from this Album.");
            return $response->withstatus(403); //Forbidden
        }
        else if($status === 2){
            $response->getbody()->write("You can't delete the last photo of an album!");
            return $response->withstatus(406); //Not Acceptable
        }
        else{
            $response->getbody()->write("Internal Error. Please, try again later.");
            return $response->withstatus(500); //Internal Server Error
        }
    }

    public function generateQRCode(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';
        $response->getBody()->write($id);
        if(!filter_var($id, FILTER_VALIDATE_INT)){
            return $response->withStatus(406); //Not acceptable
        }

        $route = "http://localhost:8080/portfolio/album/$id";
        $data = array(
            'symbology' => 'QRCode',
            'code' => $route
        );
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => json_encode($data),
                'header' =>  "Content-Type: application/json\r\n" .
                    "Accept: image/png\r\n"
            )
        );
        $context = stream_context_create($options);
        //The IPv4 for the Barcode container is 192.168.32.2
        //It may change in the future depending on how docker builds its network?
        $url = 'http://192.168.32.2/BarcodeGenerator';
        $barcodeResponse = file_get_contents($url, false, $context);
        file_put_contents("assets/img/codes/$id.png", $barcodeResponse);
        $response->getBody()->write($barcodeResponse);

        return $response->withStatus(200);
    }

    public function downloadQRCode(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';
        header('Content-Type: image/png');
        readfile("assets/img/codes/$id.png");
        exit();
    }
}
