<?php

declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Ramsey\Uuid\Uuid;
use Salle\PixSalle\Service\ValidatorService;
use Salle\PixSalle\Repository\UserRepository;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Views\Twig;


final class ProfileController {
    private Twig $twig;
    private ValidatorService $validator;
    private UserRepository $userRepository;

    public function __construct(
        Twig $twig,
        UserRepository $userRepository
    ) {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->validator = new ValidatorService();
    }

    public function showProfile(Request $request, Response $response): Response {

        $pic = $this->userRepository->getPicByEmail($_SESSION['email']);
        $user = $this->userRepository->getUserByEmail($_SESSION['email']);

        return $this->twig->render(
            $response,
            'profile.twig',
            [
                'email' => $_SESSION['email'],
                'username' => $user->username(),
                'profilePic' => $pic,
                'phoneNumber' => $user->phoneNumber()
            ]
        );
    }

    private function updateProfilePicture(&$errors){
        $currentDirectory = getcwd();
        $uploadDirectory = "/uploads/";

        $fileExtensionsAllowed = ['jpg', 'png']; // These will be the only file extensions allowed
        $fileName = $_FILES['newProfilePic']['tmp_name'];
        $fileSize = $_FILES['newProfilePic']['size'];
        $info = getimagesize($fileName);
        $fileTmpName = $_FILES['newProfilePic']['tmp_name'];
        $ext = pathinfo($_FILES['newProfilePic']['name'], PATHINFO_EXTENSION);

        $myuuid = Uuid::uuid4();    //Generate random UUID

        $uploadPath = $currentDirectory . $uploadDirectory . $myuuid;

        if (!in_array($ext, $fileExtensionsAllowed)) {
            $errors['image']['ext'] = "This file extension is not allowed. Please upload a JPG or PNG file";
        }

        if ($fileSize > 1000000) {
            $errors['image']['size'] = "File exceeds maximum size (1MB)";
        }

        if ($info[0] > 500 || $info[1] > 500) {
            $errors['image']['res'] = "Image dimensions must be less or equal to 500x500";
        }

        if (empty($errors)) {
            move_uploaded_file($fileTmpName, $uploadPath);
            $this->userRepository->updateUserProfilePicture($_SESSION['email'], $myuuid->toString());
        }

        return $myuuid;
    }

    private function validatePhoneNumber(string $phoneNumber) {
        if ((strlen($phoneNumber) != 9) || !is_numeric($phoneNumber) || !str_starts_with($phoneNumber, '6')) {
            return false;
        } else {
            return true;
        }
    }

    public function updateProfileData(Request $request, Response $response): Response
    {
        $errors = []; // Store errors here

        if (isset($_POST['submit'])) {
            //If no image has been added into the input field, retrieve the image previously stored on the DB
            if ($_FILES['newProfilePic']['size'] != 0){
                $fileName = $this->updateProfilePicture($errors);
                $imageDir = $fileName;
            } else {
                $imageDir = $this->userRepository->getPicByEmail($_SESSION['email']);
            }

            //If no username has been added into the input field, retrieve the username previously stored on the DB
            if (isset($_POST['username'])) {
                $this->userRepository->updateUsername($_SESSION['email'], $_POST['username']);
                $username =  $_POST['username'];
            } else {
                $user = $this->userRepository->getUserByEmail($_SESSION['email']);
                $username = $user->username();
            }

            if (!empty($_POST['phoneNumber'])) {
                if ($this->validatePhoneNumber($_POST['phoneNumber'])) {
                    $this->userRepository->updatePhoneNumber($_SESSION['email'], $_POST['phoneNumber']);
                    $phoneNumber =  $_POST['phoneNumber'];
                } else{
                    $errors['phone']['phoneNumber'] = "The phone number must follow the Spanish numbering plan (6XXXXXXXX)";
                    $user = $this->userRepository->getUserByEmail($_SESSION['email']);
                    $phoneNumber = $user->phoneNumber();
                }
            } else {
                $this->userRepository->updatePhoneNumber($_SESSION['email'], "");
                $phoneNumber =  "";
            }

            return $this->twig->render(
                $response,
                'profile.twig',
                [
                    'email' => $_SESSION['email'],
                    'username' => $username,
                    'profilePic' => $imageDir,
                    'phoneNumber' => $phoneNumber,
                    'errors' => $errors
                ]);
        }
        else {
            return $this->twig->render(
                $response,
                'profile.twig',
                [
                    'email' => $_SESSION['email']
                ]
            );
        }
    }
}
