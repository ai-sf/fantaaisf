<?php


return (object) [
   "use_db" => true,
  "host" => "localhost",
  "user" => "mysql",
  "password" => "mysqlpwd",
  "dbname" => "fantaaisf",
  "authentication" => (object) [
    "auth_model" => App\Models\User::class,
    "username_field" => "email",
    "password_field" => "token",
    "max_login_attempts_per_hour" => 10,
    "level_field" => "level",
    "hash_field" => "hash"
  ]
];
