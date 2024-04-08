<?php

namespace App\Controllers;

use Lepton\Core\{Application, Mailer};
use Lepton\Controller\BaseController;
use Lepton\Boson\Model;
use Liquid\{Liquid, Template};

use App\Models\{FantaBonus,FantaSettings, FantaMember, FantaPoints, User, Poll, PollAnswer, Vote, FantaTeam};
use Lepton\Authenticator\AccessControlAttributes\LoginRequired;
use Lepton\Authenticator\UserAuthenticator;
use Lepton\Http\Response\HttpResponse;

class AdminController extends BaseController
{
    public string $baseLink = "admin";
    protected array $default_parameters = [
        "nav" => [

            [
                "title" => "Home",
                "link" => "admin",
                "icon" => "house-door-fill",
                "min_level" => 2
            ],

            [
                "title" => "Utenti",
                "link" => "admin/users",
                "icon" => "people-fill",
                "min_level" => 3,
                "subnav" => [
                    [
                        "title" => "Tutti gli utenti",
                        "link" => "admin/users"
                    ],
                    [
                        "title" => "Nuovo utente",
                        "link" => "admin/users/new"
                    ]
                ]
            ],

            [
                "title" => "Fanta",
                "link" => "admin/fanta/teams",
                "icon" => "trophy-fill",
                "min_level" => 2,
                "subnav" => [
                    [
                        "title" => "Squadre",
                        "link" => "admin/fanta/teams"
                    ],
                    [
                        "title" => "Assegna bonus",
                        "link" => "admin/fanta/bonuses"
                    ]
                ]
            ],
            [
                "title" => "Frontend",
                "link" => "",
                "icon" => "arrow-left-square-fill",
                "min_level" => 2
            ]

        ]
    ];

    #[LoginRequired(2)]
    public function index()
    {
        return $this->render("Admin/index");
    }


    /* =================================== USERS ==================================== */

    #[LoginRequired(3)]
    public function usersList()
    {
        $users = User::all();
        return $this->render("Admin/Users/usersList", ["users" => $users, "num_users" => $users->count()]);
    }





    #[LoginRequired(3)]
    public function newUser()
    {
        return $this->render("Admin/Users/newUser");
    }


    #[LoginRequired(3)]
    public function saveUser()
    {

        $user = $this->doUserSave(
            id: isset($_POST["user-id"]) ? $_POST["user-id"] : null,
            email: $_POST["email"],
            level: $_POST["level"],
            name: $_POST["name"],
            surname: $_POST["surname"],
        );

        $vars = [
            "is_update" => true,
            "message" => "Utente salvato correttamente"
        ];

        if(isset($_POST["user-id"])) {
            $vars["user"] = $user;
        }

        return $this->render(
            "Admin/Users/userForm",
            $vars,
            ['HX-Trigger' => 'showToast']
        );
    }

    #[LoginRequired(3)]
    private function doUserSave($id = null, $email, $level, $name, $surname)
    {
        $user = null;
        if (! is_null($id)) {
            $user = User::get($id);
            $user->email =  $email;
            $user->level = $level;
        } else {
            $authenticator = new UserAuthenticator();
            $user = $authenticator->register($email, $level);
        }

        if ($user) {
            $user->name = $name;
            $user->surname = $surname;
            $user->save();
            return $user;
        } else {
            return 0;
        }
    }


    #[LoginRequired(3)]
    public function userBatchUpload()
    {

        if (isset($_FILES['csvfile'])) {
            $n = 0;
            $csvfile = $_FILES['csvfile']['tmp_name'];
            if (($handle = fopen($csvfile, "r")) !== false) {
                fgetcsv($handle, 1000, ",");
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    if($this->doUserSave(
                        id: null,
                        email: $data[2],
                        level: intval($data[3]),
                        name: $data[0],
                        surname: $data[1]
                    )) {
                        $n++;
                    }

                }
                fclose($handle);
            }
        }

        return new HttpResponse(
            200,
            headers: ["HX-Trigger" => "reload-users"],
            body: "<div id='post-result-inner' class='rounded container bg-success text-white py-2 px-3 small'>Caricati $n utenti</div>"
        );
    }


    #[LoginRequired(3)]
    public function sendMail(int $id)
    {
        $authenticator = new UserAuthenticator();
        $password = $authenticator->passwordReset($id);
        $user = User::get($id);
        $config = Application::getEmailConfig();
        $mail = new Mailer();

        $subject = 'Welcome to FantaGIPE!';
        $body = $this->render("Admin/loginEmail", ["name" => $user->name, "username" => $user->email, "password" => $password]);//sprintf("Username: %s <br/>Password: %s", $user->email, $password);

        if ($mail->send($user->email, $subject, $body)) {
            $message = "Email inviata correttamente";
        } else {
            $message = $mail->error;//Errore di invio";
        }
        return $this->render(
            "Admin/toaster",
            ["message" => $message],
            headers: ['HX-Trigger' => 'showToast']
        );
    }



    #[LoginRequired(3)]
    public function deleteUser($id)
    {
        $toDelete = User::get(id: $id);
        $toDelete->delete();
        $users = User::all();
        return $this->render(
            "Admin/Users/usersTable",
            [
                "users" => $users,
                "is_update" => true,
                "message" => "Utente rimosso correttamente"
            ],
            ['HX-Trigger' => 'showToast']
        );
    }


    #[LoginRequired(3)]
    public function editUser($id)
    {
        $user = User::get($id);
        return $this->render("Admin/Users/editUser", ["user" => $user]);
    }


    #[LoginRequired(3)]
    public function userSearch()
    {
        $allowed = ["name", "surname", "level", "active", "online"];
        $filters = array();

        foreach($allowed as $filter) {
            if(isset($_POST[$filter]) && $_POST[$filter] != "") {
                $filters[$filter . "__startswith"] = $_POST[$filter];
            }
        }
        if(count($filters) > 0) {
            $users = User::filter(...$filters);
        } else {
            $users = User::all(...$filters);
        }
        return $this->render("Admin/Users/usersTable", ["is_update" => true, "users" => $users, "num_users" => $users->count()]);
    }

    #[LoginRequired(3)]
    public function batchAction()
    {
        foreach($_POST["user-checkbox"] as $id) {
            switch ($_POST["action"]) {
                case 'delete':
                    $this->deleteUser($id);
                    break;
                case 'sendmail':
                    sleep(1);
                    $this->sendMail($id);
                    break;
                default:
                    break;
            }
        }
        return $this->render(
            "Admin/toaster",
            ["message" => "Azione eseguita con successo"],
            headers: ["HX-Trigger" => '{"showToast" : "", "reload-users": ""}']
        );
    }


    #[LoginRequired(2)]
    public function fantaTeams()
    {
        $users = User::filter(fanta_team__neq: "");
        $standings = array();
        $settings = FantaSettings::get(name:"has_started");
        foreach($users as $user) {
            $standings[] = [
                "id" => $user->id,
                "name" => $user->name . " " . $user->surname,
                "team_name" => $user->fanta_team,
                "points" => (new FantaController())->computePointsUser($user),
                "team" => FantaTeam::filter(user: $user)->do()
            ];
        }
        $show_points = FantaSettings::get(name:"show_points");

        usort($standings, array(FantaController::class, "cmp"));
        return $this->render("Admin/Fanta/league", ["has_started" => $settings->value, "users" => $standings,
        "num_teams" => $users->count(), "show_points" => $show_points->value]);
    }

    #[LoginRequired(2)]
    public function fantaBonuses()
    {
        $members = FantaMember::all()->order_by("name");
        return $this->render("Admin/Fanta/bonuses", ["members" => $members]);
    }

    #[LoginRequired(2)]
    public function fantaBonusesMember($id)
    {
        $member = FantaMember::get($id);
        $bonuses = FantaBonus::all()->order_by("id");
        $memberBonuses = array();

        foreach($bonuses as $bonus) {
            $counts = FantaPoints::filter(member: $member, bonus: $bonus)->count();
            $memberBonuses[] = [
                "id" => $bonus->id,
                "name" => $bonus->name,
                "points" => $bonus->points,
                "times" => $counts
            ];
        }

        return $this->render("Admin/Fanta/memberBonuses", ["member" => $member, "bonuses" => $memberBonuses]);
    }


    #[LoginRequired(2)]
    public function setBonus($member_id, $bonus_id)
    {
        $member = FantaMember::get($member_id);
        $bonus = FantaBonus::get($bonus_id);
        $points = FantaPoints::new(member: $member, bonus: $bonus);
        $points->save();

        return $this->render(
            "Admin/toaster",
            ["message" => "Bonus assegnato correttamente"],
            headers: ['HX-Trigger' => 'showToast']
        );
    }



    #[LoginRequired(2)]
    public function removeBonus($member_id, $bonus_id)
    {
        $member = FantaMember::get($member_id);
        $bonus = FantaBonus::get($bonus_id);
        $points = FantaPoints::filter(member: $member, bonus: $bonus);
        if($points->count() > 0) {
            $points->first()->delete();
            return $this->render(
                "Admin/toaster",
                ["message" => "Bonus rimosso correttamente"],
                headers: ['HX-Trigger' => 'showToast']
            );
        }
        return new HttpResponse(200, body: "");

    }





    #[LoginRequired(3)]
    public function startGame()
    {
        $setting = FantaSettings::get(name: "has_started");
        $setting->value = array_key_exists("has_started", $_POST) ? 1 : 0;
        $setting->save();

        $text = $setting->value ? "aperta" : "chiusa";

        return $this->render(
            "Admin/toaster",
            ["message" => "Gara $text!"],
            headers: ['HX-Trigger' => 'showToast']
        );
    }

    #[LoginRequired(3)]
    public function showPoints() {
       $setting = FantaSettings::get(name: "show_points");
        $setting->value = array_key_exists("show_points", $_POST) ? 1 : 0;
        $setting->save();

        $text = $setting->value ? "mostrati" : "nascosti";

        return $this->render(
            "Admin/toaster",
            ["message" => "Punti $text!"],
            headers: ['HX-Trigger' => 'showToast']
        );
    }


}
