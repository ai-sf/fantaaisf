<?php
namespace App\Models;

use JetBrains\PhpStorm\Deprecated;
use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{PrimaryKey, ForeignKey};

class FantaTeam extends Model{

  protected static $tableName = "fanta_user_has_team";

  #[PrimaryKey] protected $id;
  #[ForeignKey(User::class)] protected $user;
  #[ForeignKey(FantaMember::class)] protected $teamMember;
}