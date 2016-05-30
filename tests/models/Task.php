<?php
use Mpociot\Teamwork\Traits\UsedByTeams;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use UsedByTeams;
}
