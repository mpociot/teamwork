<?php

namespace Mpociot\Teamwork\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Mpociot\Teamwork\Traits\UsedByTeams;

class Task extends Model
{
    use UsedByTeams;
}
