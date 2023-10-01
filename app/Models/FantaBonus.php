<?php

namespace App\Models;

use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{TextField, NumberField, PrimaryKey, CharField};

class FantaBonus extends Model
{
    protected static $tableName = "fanta_bonusmalus";

    #[PrimaryKey] protected $id;
    #[TextField] protected $name;
    #[NumberField] protected $points;
}
