<?php

namespace App\Models;

use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{CharField, NumberField, PrimaryKey};

class FantaSettings extends Model
{
    protected static $tableName = "fanta_settings";

    #[PrimaryKey] protected $id;
    #[CharField] protected $name;
    #[NumberField] protected $value;
}
