<?php
declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Salle\PixSalle\Repository\UserRepository;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Views\Twig;


final class WalletController {
    private Twig $twig;
    private UserRepository $userRepository;

    public function __construct(
        Twig $twig,
        UserRepository $userRepository
    ) {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function showWallet(Request $request, Response $response): Response {
        if(isset($_SESSION['email'])){
            $imageDir = $this->userRepository->getPicByEmail($_SESSION['email']);
        }else{
            $imageDir = "default.png";
        }

        return $this->twig->render(
            $response,
            'wallet.twig',
            [
                'profilePic' => $imageDir,
                'email' => $_SESSION['email'],
                'funds' => $this->userRepository->getUserFunds($_SESSION['email'])
            ]
        );
    }

    public function addFunds(Request $request, Response $response): Response {
        $parsedBody = $request->getParsedBody();
        if(!isset($parsedBody['funds'])){
            $response->getBody()->write("Error: Bad request. Please, refresh the page and try again");
            return $response->withStatus(400); //Return bad request if funds doesn't exist
        }

        $funds = $request->getParsedBody()['funds'];
        if($funds <= 0 || $funds > 1000){
            $response->getBody()->write("Error. You can't add this money!");
            return $response->withStatus(400); //Return bad request if the attribute funds isn't valid
        }

        //If everything is OK, update the funds
        $successfulUpdate = $this->userRepository->updateUserFunds($_SESSION['email'], floatval($funds));

        if($successfulUpdate){
            return $response->withStatus(200); //Return everything OK
        }
        else{
            $response->getBody()->write("Database error. Please, try again later");
            return $response->withStatus(500); //We haven't been able to update (database error)
        }
    }
}
