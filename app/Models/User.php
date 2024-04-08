<?php

namespace App\Models;

use JetBrains\PhpStorm\Deprecated;
use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{CharField, DateTimeField, NumberField, PrimaryKey, ForeignKey};

class User extends Model
{
    protected static $tableName = "users";

    #[PrimaryKey] protected $id;
    #[CharField] protected $name;
    #[CharField] protected $surname;
    #[CharField(max_length: 256)] protected $password;
    #[CharField(max_length: 64)] protected $email;
    #[CharField] protected $hash;
    #[NumberField] protected $level;
    #[NumberField] protected $active;
    #[DateTimeField] protected $last_login;
    #[NumberField] protected $fanta_budget;
    #[CharField] protected $fanta_team;
    #[ForeignKey(FantaMember::class)] protected $fanta_captain;
    #[NumberField] protected $online;

}
