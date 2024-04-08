<?php

namespace App\Controllers;

use Lepton\Authenticator\UserAuthenticator;
use Lepton\Controller\BaseController;
use Liquid\{Liquid, Template};

use App\Models\{User, FantaMember, FantaBonus, FantaTeam, FantaPoints, FantaSettings};
use Lepton\Authenticator\AccessControlAttributes\LoginRequired;
use Lepton\Http\Response\HttpResponse;

class FantaController extends BaseController
{
    public string $baseLink = "";
    protected array $default_parameters = [
            "nav" => [

                [
                    "title" => "My team",
                    "link" => "fanta",
                    "icon" => "people-fill",
                    "min_level" => 1
                ],
                [
                    "title" => "FantaGIPE League",
                    "link" => "fanta/league",
                    "min_level" => 1,
                    "icon" => "bar-chart"
                ],
                [
                    "title" => "Bonuses & penalties",
                    "link" => "fanta/bonusmalus",
                    "min_level" => 1,
                    "icon" => "plus-slash-minus"

                ],
                [
                    "title" => "Admin",
                    "link" => "admin",
                    "icon" => "tools",
                    "min_level" => 2
                ]



        ]
    ];



    #[LoginRequired(1)]
    public function index()
    {
        $setting = FantaSettings::get(name: "has_started");
        return $this->render("Fanta/index", ["can_edit" => 1 - $setting->value]);
    }


    #[LoginRequired(1)]
    public function toggle($id)
    {
        $setting = FantaSettings::get(name: "has_started");
        if($setting->value == 0) {
            $user = (new UserAuthenticator())->getLoggedUser();
            $member = FantaMember::get($id);
            $price = 15;
            $exists = FantaTeam::filter(user: $user, teamMember: $member)->count();
            $team_number = FantaTeam::filter(user: $user)->count();
            if($exists) {
                FantaTeam::get(user: $user, teamMember: $member)->delete();
                echo "cancello";
                $user->fanta_budget += $price;
                if(($user->fanta_captain instanceof FantaMember)  && ($user->fanta_captain->id == $id)) {
                    $user->fanta_captain = null;
                }
                $user->save();

            } else {
                if(($user->fanta_budget >= $price) && ($team_number < 5)) {
                    $teamAssociation = FantaTeam::new(user: $user, teamMember: $member);
                    $teamAssociation->save();
                    $user->fanta_budget -= $price;
                    if(!$user->fanta_captain) {
                        $user->fanta_captain = $member;
                    }
                    $user->save();
                } else {
                    exit;
                }

            }
            return new HttpResponse(200);
        }
        return new HttpResponse(200);

    }


    #[LoginRequired(1)]
    public function saveName()
    {
        $setting = FantaSettings::get(name: "has_started");
        if($setting->value == 0) {
            $user = (new UserAuthenticator())->getLoggedUser();
            $user->fanta_team = $_POST["team_name"];
            $user->save();

            return $this->showTeam(1);
        } else {
            return new HttpResponse(201);
        }
    }



    #[LoginRequired(1)]
    public function showTeam($update)
    {
        $user = (new UserAuthenticator())->getLoggedUser();
        $membriEC = FantaMember::filter(role: 1);
        $membriLC = FantaMember::filter(role: 2);
        $membriOC = FantaMember::filter(role: 3);
        $mymembers = FantaTeam::filter(user: $user);
        $selected = array();
        foreach($mymembers as $member) {
            $selected[] = $member->teamMember->id;
        }

        $setting = FantaSettings::get(name: "has_started");

        $data = [ "EC" => $membriEC,
        "LC" => $membriLC,
        "OC" => $membriOC,
        "selected" => $selected,
        "user" => $user,
        "can_edit" => 1 - $setting->value];

        $headers = array();

        if($update == 1) {
            $data["show_toast"] = true;
            $data["is_update"] = true;
            $headers["HX-Trigger"] = "showToast";
        }
        return $this->render("Fanta/myteam", $data, headers: $headers);
    }


    public static function cmp($a, $b)
    {
        return $a["points"] < $b["points"];
    }

    #[LoginRequired(1)]
    public function league()
    {
        $users = User::filter(fanta_team__neq: "");
        $standings = array();

        foreach($users as $user) {
            $points = $this->computePointsUser($user);
            $standings[] = [
                "id" => $user->id,
                "name" => $user->name." ".$user->surname,
                "team_name" => $user->fanta_team,
                "captain" => $user->fanta_captain->id,
                "points" => $points,
                "team" => FantaTeam::filter(user: $user)->do(),
                "position" => 0
            ];

        }

        $last_points = -1;
        $last_position = 0;

        usort($standings, array(self::class, "cmp"));

        foreach($standings as $key => &$standing) {
            if($key == 0) {
                $standing["position"] = 1;
            } elseif($last_points > $standing["points"]) {
                $standing["position"] = $last_position + 1;
            } else {
                $standing["position"] = $last_position;
            }


            $last_points = $standing["points"];
            $last_position = $standing["position"];
        }

        $setting = FantaSettings::get(name:"has_started");
        $points = FantaSettings::get(name: "show_points");
        return $this->render("Fanta/league", ["users" => $standings,
        "num_teams" => $users->count(), "has_started" => $setting->value, "show_points" => $points->value]);
    }


    #[LoginRequired(1)]
    public function computePointsUser($user)
    {
        $team = FantaTeam::filter(user: $user);
        $points = 0;
        foreach($team as $member) {

            $multiplier = $member->teamMember == $user->fanta_captain ? 2 : 1;
            $points += $multiplier * $this->computePointsMember($member->teamMember);
        }
        return $points;

    }


    #[LoginRequired(1)]
    public function computePointsMember($member)
    {
        $memberbonus = FantaPoints::filter(member: $member);
        $points = 0;
        foreach($memberbonus as $bonus) {
            $points += $bonus->bonus->points * $bonus->multiplier;
        }
        return $points;
    }

    #[LoginRequired(1)]
    public function bonusMalus()
    {
        $members = FantaMember::all()->order_by("name");
        $members_array = array();
        foreach($members as $member) {
            $bonuses = FantaPoints::filter(member: $member);
            $points = 0;
            foreach($bonuses as $bonus) {
                $points += $bonus->bonus->points;
            }
            $members_array[] = [
                    "id" => $member->id,
                    "name" => $member->name,
                    "photo" => $member->photo,
                    "description" => $member->description,
                    "points" => $points
                ];
        }
        usort($members_array, array(self::class, "cmp"));
        return $this->render("Fanta/bonusMalus", ["members" => $members_array]);
    }

    #[LoginRequired(1)]
    public function fantaBonusesMember($id)
    {
        $member = FantaMember::get($id);
        $bonuses = FantaBonus::all()->order_by("id");
        $memberBonuses = array();

        foreach($bonuses as $bonus) {
            $counts = FantaPoints::filter(member: $member, bonus: $bonus)->count();
            if($counts > 0) {
                $memberBonuses[] = [
                    "id" => $bonus->id,
                    "name" => $bonus->name,
                    "points" => $bonus->points,
                    "times" => $counts
                ];
            }
        }

        return $this->render("Fanta/memberBonuses", ["member" => $member, "bonuses" => $memberBonuses]);
    }

}
