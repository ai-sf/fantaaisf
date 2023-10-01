<?php

namespace App\Models;

use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{ForeignKey, TextField, NumberField, PrimaryKey};

class FantaPoints extends Model
{
    protected static $tableName = "fanta_points";

    #[PrimaryKey] protected $id;
    #[ForeignKey(FantaMember::class)] protected $member;
    #[ForeignKey(FantaBonus::class)] protected $bonus;
    #[NumberField] protected $multiplier;
}
