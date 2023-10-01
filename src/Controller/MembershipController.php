<?php
declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Salle\PixSalle\Model\User;
use Salle\PixSalle\Repository\UserRepository;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Views\Twig;


final class MembershipController {
    private Twig $twig;
    private UserRepository $userRepository;

    public function __construct(
        Twig $twig,
        UserRepository $userRepository
    ) {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function showMembership(Request $request, Response $response): Response {
        if(isset($_SESSION['email'])){
            $imageDir = $this->userRepository->getPicByEmail($_SESSION['email']);
        }else{
            $imageDir = "default.png";
        }
        return $this->twig->render(
            $response,
            'membership.twig',
            [
                'profilePic' => $imageDir,
                'email' => $_SESSION['email'],
                'membership' => $this->userRepository->getUserMembership($_SESSION['email'])
            ]
        );
    }

    public function updateMembership(Request $request, Response $response): Response {
        $parsedBody = $request->getParsedBody();
        if(!isset($parsedBody['membership'])){
            $response->getBody()->write("Error: Bad request. Please, refresh the page and try again");
            return $response->withStatus(400); //Return bad request if the attribute membership is not present in the body
        }

        $desiredMembership = $request->getParsedBody()['membership'];
        if(User::validMembershipValue($desiredMembership) === false){
            $response->getBody()->write("Error: Bad request. Please, refresh the page and try again");
            return $response->withStatus(400); //Return bad request if the attribute membership doesn't have a valid value
        }

        $currentMembership = $this->userRepository->getUserMembership($_SESSION['email']);

        if($currentMembership == $desiredMembership){
            $response->getBody()->write("Error: Bad request. Please, refresh the page and try again");
            return $response->withStatus(400); //Return bad request if the new membership is the same as the old one
        }

        //If everything is OK, update the membership
        $successfulUpdate = $this->userRepository->updateUserMembership($_SESSION['email'], intval($desiredMembership));

        if($successfulUpdate){
            return $response->withStatus(200); //Return everything OK
        }
        else{
            $response->getBody()->write("Database error. Please, try again later");
            return $response->withStatus(500); //We haven't been able to update (database error)
        }
    }

}
