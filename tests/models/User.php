<?php
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable {
    use \Mpociot\Teamwork\Traits\UserHasTeams;
}