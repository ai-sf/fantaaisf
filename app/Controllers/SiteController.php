<?php

namespace App\Controllers;

use Lepton\Core\Application;
use Lepton\Authenticator\UserAuthenticator;
use Lepton\Controller\BaseController;
use Liquid\{Liquid, Template};

use App\Models\{User, Poll, PollAnswer, Vote};
use Lepton\Authenticator\AccessControlAttributes\LoginRequired;
use Lepton\Http\Response\HTTPResponse;

class SiteController extends BaseController
{
    public string $baseLink = "";
    protected array $default_parameters = [
        "nav" => [
            [
                "title" => "Fanta",
                "link" => "fanta",
                "icon" => "trophy-fill",
                "min_level" => 1
            ],
            [
                "title" => "Admin",
                "link" => "admin",
                "icon" => "tools",
                "min_level" => 2
            ]

        ]
    ];




    public function login()
    {
        $post = Application::$request->post;
        if (isset($post["email"]) && isset($post["password"])) {
            $authenticator = new UserAuthenticator();
            if (!$authenticator->login($post["email"], $post["password"])) {
                return $this->render(
                    "Site/login_form",
                    [
                      "login_invalid" => true,
                      "login_message" => "Wrong email and/or password"
                    ]
                );
            } else {
                if (isset($_SESSION["redirect_url"])) {
                    $response = $this->redirect($_SESSION["redirect_url"], htmx:true, parse:false);
                    unset($_SESSION['redirect_url']);
                    return $response;
                }
                return $this->redirect("", htmx:true);
            }
        }
        return $this->render("Site/login");
    }


    #[LoginRequired(1)]
    public function logout()
    {
        $authenticator = new UserAuthenticator();
        $authenticator->logout();
        return $this->redirect("login");
    }



}
