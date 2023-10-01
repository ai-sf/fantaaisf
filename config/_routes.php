<?php

use App\Controllers\AdminController;
use App\Controllers\FantaController;
use App\Controllers\SiteController;

return [
  "" => [FantaController::class, "index"],
  "login" => [SiteController::class, "login"],
  "logout" => [SiteController::class, "logout"],

  "polls/<int:id>" => [SiteController::class, "poll"],
  "polls/vote" => [SiteController::class, "pollVote"],

  "admin" => [AdminController::class, "index"],

  "admin/users" => [AdminController::class, "usersList"],
  "admin/users/new" => [AdminController::class, "newUser"],
  "admin/users/save" => [AdminController::class, "saveUser"],
  "admin/users/activate/<int:id>?status=<int:status>" => [AdminController::class, "activateUser"],
  "admin/users/activate/<int:id>" => [AdminController::class, "activateUser"],
  "admin/users/sendMail/<int:id>" => [AdminController::class, "sendMail"],
  "admin/users/delete/<int:id>" => [AdminController::class, "deleteUser"],
  "admin/users/edit/<int:id>" =>  [AdminController::class, "editUser"],
  "admin/users/batchAction" => [AdminController::class, "batchAction"],
  "admin/users/toggleOnline/<int:id>" => [AdminController::class, "toggleOnline"],
  "admin/users/toggleOnline/<int:id>?online=<int:online>" => [AdminController::class, "toggleOnline"],
  "admin/users/search" => [AdminController::class, "userSearch"],
  "admin/users/batchUpload" => [AdminController::class, "userBatchUpload"],
  "admin/loginEmail" => [AdminController::class, "loginEmailPreview"],


  "admin/fanta/teams" => [AdminController::class, "fantaTeams"],
  "admin/fanta/bonuses" => [AdminController::class, "fantaBonuses"],
  "admin/fanta/bonusesMember/<int:id>" => [AdminController::class, "fantaBonusesMember"],
  "admin/fanta/setBonus/<int:member_id>/<int:bonus_id>" => [AdminController::class, "setBonus"],
  "admin/fanta/removeBonus/<int:member_id>/<int:bonus_id>" => [AdminController::class, "removeBonus"],
  "admin/fanta/startGame" => [AdminController::class, "startGame"],

  "fanta" => [FantaController::class, "index"],
  "fanta/toggle/<int:id>" => [FantaController::class, "toggle"],
  "fanta/saveteamname" => [FantaController::class, "saveName"],
  "fanta/league" => [FantaController::class, "league"],
  "fanta/myteam/<int:update>" => [FantaController::class, "showTeam"],
  "fanta/bonusmalus" => [FantaController::class, "bonusMalus"],
  "fanta/bonusesMember/<int:id>" => [FantaController::class, "fantaBonusesMember"]

];
