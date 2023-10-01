<?php

declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salle\PixSalle\Repository\PortfolioRepository;
use Salle\PixSalle\Repository\UserRepository;
use Slim\Views\Twig;


final class HomeController {
    private Twig $twig;
    private PortfolioRepository $portfolioRepository;
    private UserRepository $userRepository;

    public function __construct(Twig $twig, PortfolioRepository $portfolioRepository, UserRepository $userRepository) {
        $this->twig = $twig;
        $this->portfolioRepository = $portfolioRepository;
        $this->userRepository = $userRepository;
    }

    public function showHome(Request $request, Response $response): Response {
        $recommendedAlbums = $this->portfolioRepository->getRecommendedAlbums();
        if(isset($_SESSION['email'])){
            $imageDir = $this->userRepository->getPicByEmail($_SESSION['email']);
        }else{
            $imageDir = "default.png";
        }
        return $this->twig->render($response,
            'home.twig',
            [
                'email' => $_SESSION['email'] ?? '',
                'profilePic' => $imageDir,
                'albums' => $recommendedAlbums
            ]
        );
    }
}
