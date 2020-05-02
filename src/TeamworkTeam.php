<?php namespace Mpociot\Teamwork;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Mpociot\Teamwork\Traits\TeamworkTeamTrait;


class TeamworkTeam extends Model
{
    use TeamworkTeamTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $fillable = ['name', 'owner_id'];

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct( array $attributes = [ ] )
    {
        parent::__construct( $attributes );
        $this->table = Config::get( 'teamwork.teams_table' );
    }
}
