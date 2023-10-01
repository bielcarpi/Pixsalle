<?php

declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Salle\PixSalle\Service\ValidatorService;
use Salle\PixSalle\Repository\UserRepository;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;


final class PwdController {
    private Twig $twig;
    private UserRepository $userRepository;
    private ValidatorService $validator;

    public function __construct(
        Twig $twig,
        UserRepository $userRepository
    ) {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->validator = new ValidatorService();
    }

    public function showPwdSettings(Request $request, Response $response): Response {
        return $this->twig->render(
            $response,
            'change-password.twig',
            [
                'email' => $_SESSION['email']
            ]
        );
    }

    public function updatePassword(Request $request, Response $response): Response {
        $errors = []; // Store errors here

        if (isset($_POST['submit'])) {
            $oldPasswordAux = md5($_POST['oldPassword']);
            $newPassword = md5($_POST['newPassword']);

            $user = $this->userRepository->getUserByEmail($_SESSION['email']);

            if (strcmp($oldPasswordAux, $user->password()) != 0) {
                $errors['passwordError'] = "Error. The new password can't be the old one.";
            } else {
                if (strcmp($_POST['newPassword'], $_POST['repNewPassword']) != 0) {
                    $errors['passwordError'] = "Error. New passwords don't match.";
                } else {
                    if ((empty($_POST['newPassword'])) || (strlen($_POST['newPassword']) < 6) || (!preg_match("~[0-9]+~", $_POST['newPassword'])) || (!preg_match("/[a-z]/", $_POST['newPassword'])) || (!preg_match("/[A-Z]/", $_POST['newPassword']))) {
                        $errors['passwordError'] = "There has been an error changing the password";
                    } else {
                        $this->userRepository->updatePassword($_SESSION['email'], $newPassword);
                        $errors['ok'] = "Password has been changed successfuly";
                    }
                }
            }

            return $this->twig->render(
                $response,
                'change-password.twig',
                [
                    'email' => $_SESSION['email'],
                    'errors' => $errors
                ]);

        }
        else {
            return $this->twig->render(
                $response,
                'change-password.twig',
                [
                    'email' => $_SESSION['email']
                ]
            );
        }
    }
}
