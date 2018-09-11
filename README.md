# Teamwork (Laravel 5 Package)

[![Latest Version](https://img.shields.io/packagist/v/mpociot/teamwork.svg)](https://github.com/mpociot/teamwork/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://travis-ci.org/mpociot/teamwork.svg)](https://travis-ci.org/mpociot/teamwork)
[![codecov.io](https://codecov.io/github/mpociot/teamwork/coverage.svg?branch=master)](https://codecov.io/github/mpociot/teamwork?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a2a26e55-bfc7-49a9-933b-72ca7c245034/mini.png)](https://insight.sensiolabs.com/projects/a2a26e55-bfc7-49a9-933b-72ca7c245034)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mpociot/teamwork/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mpociot/teamwork/?branch=master)

Teamwork is the fastest and easiest method to add a User / Team association with Invites to your **Laravel 5** project.

## Contents

- [Installation](#installation)
- [Configuration](#configuration)
    - [User relation to teams](#user-relation-to-team)
    - [Models](#models)
        - [Team](#team)
        - [User](#user)
- [Usage](#usage)
    - [Scaffoling](#scaffolding)
    - [Basic concepts](#basic-concepts)
    - [Get to know my team(s)](#know-my-teams)
    - [Team owner](#team-owner)
    - [Switching the current team](#switching-the-current-team)
    - [Inviting others](#inviting-others)
    - [Accepting invites](#accepting-invites)
    - [Denying invites](#denying-invites)
    - [Attach/Detach/Invite Events](#events)
    - [Limit Models to current Team](#scope)
- [License](#license)

<a name="installation" />

## Installation

## For Laravel 5.4

    "mpociot/teamwork": "~5.0"

## For Laravel 5.3

    "mpociot/teamwork": "~4.0"

## For Laravel 5.2

    "mpociot/teamwork": "~3.0"

## For Laravel 5.0 / 5.1

    "mpociot/teamwork": "~2.0"


Add the version you need to your composer.json. Then run `composer install` or `composer update`.

(or run `composer require mpociot/teamwork` if you prefere that)

Then in your `config/app.php` add

    Mpociot\Teamwork\TeamworkServiceProvider::class,

in the `providers` array.

The `Teamwork` Facade will be installed automatically within the Service Provider.

<a name="configuration"/>

## Configuration

To publish Teamwork's configuration and migration files, run the `vendor:publish` command.

```bash
php artisan vendor:publish --provider="Mpociot\Teamwork\TeamworkServiceProvider"
```

This will create a `teamwork.php` in your config directory.
The default configuration should work just fine for you, but you can take a look at it, if you want to customize the table / model names Teamwork will use.

<a name="user-relation-to-team" />

### User relation to teams

Run the `migration` command, to generate all tables needed for Teamwork.
**If your users are stored in a different table other than `users` be sure to modify the published migration.**


```bash
php artisan migrate
```

After the migration, 3 new tables will be created:

- teams &mdash; stores team records
- team_user &mdash; stores [many-to-many](http://laravel.com/docs/5.1/eloquent-relationships#many-to-many) relations between users and teams
- team_invites &mdash; stores pending invites for email addresses to teams

You will also notice that a new column `current_team_id` has been added to your users table.
This column will define the Team, the user is currently assigned to.

<a name="models" />

### Models

<a name="team" />

#### Team

Create a Team model inside `app/Team.php` using the following example:

```php
<?php namespace App;

use Mpociot\Teamwork\TeamworkTeam;

class Team extends TeamworkTeam
{
}
```

The `Team` model has two main attributes:

- `owner_id` &mdash; Reference to the User model that owns this Team.
- `name` &mdash; Human readable name for the Team.

The `owner_id` is an optional attribute and is nullable in the database.

When extending TeamworkTeam, remember to change the `team_model` variable in `config/teamwork.php` to your new model. For instance: `'team_model' => App\Team::class`

<a name="user" />

#### User

Add the `UserHasTeams` trait to your existing User model:

```php
<?php namespace App;

use Mpociot\Teamwork\Traits\UserHasTeams;

class User extends Model {

	use UserHasTeams; // Add this trait to your model
}
```

This will enable the relation with `Team` and add the following methods `teams()`, `ownedTeams()` `currentTeam()`, `invites()`, `isTeamOwner()`, `isOwnerOfTeam($team)`, `attachTeam($team, $pivotData = [])`, `detachTeam($team)`, `attachTeams($teams)`, `detachTeams($teams)`, `switchTeam($team)` within your `User` model.

Don't forget to dump composer autoload

```bash
composer dump-autoload
```

<a name="middleware">

### Middleware

If you would like to use the middleware to protect to current team owner then just add the middleware provider to your `app\Http\Kernel.php` file.

```php
    protected $routeMiddleware = [
        ...
        'teamowner' => \Mpociot\Teamwork\Middleware\TeamOwner::class,
        ...
    ];
```

Afterwards you can use the `teamowner` middleware in your routes file like so.

```php
Route::get('/owner', function(){
    return "Owner of current team.";
})->middleware('auth', 'teamowner');
```

Now only if the authenticated user is the owner of the current team can access that route.

> This middleware is aimed to protect routes where only the owner of the team can edit/create/delete that model

**And you are ready to go.**

<a name="usage" />

## Usage

<a name="scaffolding" />

### Scaffolding

The easiest way to give your new Laravel project Team abilities is by using the `make:teamwork` command.

```bash
php artisan make:teamwork
```

This command will create all views, routes and controllers to make your new project team-ready.

Out of the box, the following parts will be created for you:

* Team listing
* Team creation / editing / deletion
* Invite new members to teams

Imagine it as a the `make:auth` command for Teamwork.

To get started, take a look at the new installed `/teams` route in your project.

<a name="basic-concepts" />

### Basic concepts

Let's start by creating two different Teams.

```php
$team	= new Team();
$team->owner_id = User::where('username', '=', 'sebastian')->first()->getKey();
$team->name = 'My awesome team';
$team->save();

$myOtherCompany = new Team();
$myOtherCompany->owner_id = User::where('username', '=', 'marcel')->first()->getKey();
$myOtherCompany->name = 'My other awesome team';
$myOtherCompany->save();
```

Now thanks to the `UserHasTeams` trait, assigning the Teams to the user is uber easy:

```php
$user = User::where('username', '=', 'sebastian')->first();

// team attach alias
$user->attachTeam($team, $pivotData); // First parameter can be a Team object, array, or id

// or eloquent's original technique
$user->teams()->attach($team->id); // id only
```

By using the `attachTeam` method, if the User has no Teams assigned, the `current_team_id` column will automatically be set.

<a name="know-my-teams" />

### Get to know my team(s)

The currently assigned Team of a user can be accessed through the `currentTeam` relation like this:

```php
echo "I'm currently in team: " . Auth::user()->currentTeam->name;
echo "The team owner is: " . Auth::user()->currentTeam->owner->username;

echo "I also have these teams: ";
print_r( Auth::user()->teams );

echo "I am the owner of these teams: ";
print_r( Auth::user()->ownedTeams );

echo "My team has " . Auth::user()->currentTeam->users->count() . " users.";
```

The `Team` model has access to these methods:

- `invites()` &mdash; Returns a many-to-many relation to associated invitations.
- `users()` &mdash; Returns a many-to-many relation with all users associated to this team.
- `owner()` &mdash; Returns a one-to-one relation with the User model that owns this team.
- `hasUser(User $user)` &mdash; Helper function to determine if a user is a teammember

<a name="team-owner" />

### Team owner

If you need to check if the User is a team owner (regardless of the current team) use the `isTeamOwner()` method on the User model.

```php
if( Auth::user()->isTeamOwner() )
{
	echo "I'm a team owner. Please let me pay more.";
}
```
Additionally if you need to check if the user is the owner of a specific team, use:

```php
$team = Auth::user()->currentTeam;
if( Auth::user()->isOwnerOfTeam( $team ) )
{
	echo "I'm a specific team owner. Please let me pay even more.";
}
```

The `isOwnerOfTeam` method also allows an array or id as team parameter.

<a name="switching-the-current-team" />

### Switching the current team

If your Users are members of multiple teams you might want to give them access to a `switch team` mechanic in some way.

This means that the user has one "active" team, that is currently assigned to the user. All other teams still remain attached to the relation!

Glad we have the `UserHasTeams` trait.

```php
try {
	Auth::user()->switchTeam( $team_id );
	// Or remove a team association at all
	Auth::user()->switchTeam( null );
} catch( UserNotInTeamException $e )
{
	// Given team is not allowed for the user
}
```

Just like the `isOwnerOfTeam` method, `switchTeam` accepts a Team object, array, id or null as a parameter.

<a name="inviting-others" />

### Inviting others

The best team is of no avail if you're the only team member.

To invite other users to your teams, use the `Teamwork` facade.

```php
Teamwork::inviteToTeam( $email, $team, function( $invite )
{
	// Send email to user / let them know that they got invited
});
```

You can also send invites by providing an object with an `email` property like:

```php
$user = Auth::user();

Teamwork::inviteToTeam( $user , $team, function( $invite )
{
	// Send email to user / let them know that they got invited
});
```


This method will create a `TeamInvite` model and return it in the callable third parameter.

This model has these attributes:

- `email` &mdash;  The email that was invited.
- `accept_token` &mdash; Unique token used to accept the invite.
- `deny_token` &mdash; Unique token used to deny the invite.

In addition to these attributes, the model has these relations:

- `user()` &mdash; one-to-one relation using the `email` as a unique identifier on the User model.
- `team()` &mdash; one-to-one relation return the Team, that invite was aiming for.
- `inviter()` &mdash; one-to-one relation return the User, that created the invite.

**Note:**
The `inviteToTeam` method will **not** check if the given email already has a pending invite. To check for pending invites use the `hasPendingInvite` method on the `Teamwork` facade.

Example usage:

```php
if( !Teamwork::hasPendingInvite( $request->email, $request->team) )
{
	Teamwork::inviteToTeam( $request->email, $request->team, function( $invite )
	{
                // Send email to user
	});
} else {
	// Return error - user already invited
}
```

<a name="accepting-invites" />

### Accepting invites

Once you invited other users to join your team, in order to accept the invitation use the `Teamwork` facade once again.

```php
$invite = Teamwork::getInviteFromAcceptToken( $request->token ); // Returns a TeamworkInvite model or null

if( $invite ) // valid token found
{
	Teamwork::acceptInvite( $invite );
}
```

The `acceptInvite` method does two thing:

- Call `attachTeam` with the invite-team on the currently authenticated user.
- Delete the invitation afterwards.

<a name="denying-invites" />

### Denying invites

Just like accepting invites:

```php
$invite = Teamwork::getInviteFromDenyToken( $request->token ); // Returns a TeamworkInvite model or null

if( $invite ) // valid token found
{
	Teamwork::denyInvite( $invite );
}
```

The `denyInvite` method is only responsible for deleting the invitation from the database.

<a name="events" />

### Attaching/Detaching/Invite Events

If you need to run additional processes after attaching/detaching a team from a user or inviting a user, you can Listen for these events:

```php
\Mpociot\Teamwork\Events\UserJoinedTeam

\Mpociot\Teamwork\Events\UserLeftTeam

\Mpociot\Teamwork\Events\UserInvitedToTeam
```

In your `EventServiceProvider` add your listener(s):

```php
/**
 * The event listener mappings for the application.
 *
 * @var array
 */
protected $listen = [
    ...
    \Mpociot\Teamwork\Events\UserJoinedTeam::class => [
        App\Listeners\YourJoinedTeamListener::class,
    ],
    \Mpociot\Teamwork\Events\UserLeftTeam::class => [
        App\Listeners\YourLeftTeamListener::class,
    ],
    \Mpociot\Teamwork\Events\UserInvitedToTeam::class => [
        App\Listeners\YourUserInvitedToTeamListener::class,
    ],
];
```

The UserJoinedTeam and UserLeftTeam event exposes the User and Team's ID. In your listener, you can access them like so:

```php
<?php

namespace App\Listeners;

use Mpociot\Teamwork\Events\UserJoinedTeam;

class YourJoinedTeamListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserJoinedTeam  $event
     * @return void
     */
    public function handle(UserJoinedTeam $event)
    {
        // $user = $event->getUser();
        // $teamId = $event->getTeamId();
        
        // Do something with the user and team ID.
    }
}
```

The UserInvitedToTeam event contains an invite object which could be accessed like this:

```php
<?php

namespace App\Listeners;

use Mpociot\Teamwork\Events\UserInvitedToTeam;

class YourUserInvitedToTeamListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserInvitedToTeam  $event
     * @return void
     */
    public function handle(UserInvitedToTeam $event)
    {
        // $user = $event->getInvite()->user;
        // $teamId = $event->getTeamId();
        
        // Do something with the user and team ID.
    }
}
```

<a name="scope" />

### Limit Models to current Team

If your models are somehow limited to the current team you will find yourself writing this query over and over again: `Model::where('team_id', auth()->user()->currentTeam->id)->get();`.

To automate this process, you can let your models use the `UsedByTeams` trait. This trait will automatically append the current team id of the authenticated user to all queries and will also add it to a field called `team_id` when saving the models.

**Note:**
> This assumes that the model has a field called `team_id`

#### Usage

```php
use Mpociot\Teamwork\Traits\UsedByTeams;

class Task extends Model
{
    use UsedByTeams;
}
```

When using this trait, all queries will append `WHERE team_id=CURRENT_TEAM_ID`. 
If theres a place in your app, where you really want to retrieve all models, no matter what team they belong to, you can use the `allTeams` scope.

**Example:**

```php
// gets all tasks for the currently active team of the authenticated user
Task::all();

// gets all tasks from all teams globally
Task::allTeams()->get();
```


<a name="license" />

## License

Teamwork is free software distributed under the terms of the MIT license.

'Marvel Avengers' image licensed under [Creative Commons 2.0](https://creativecommons.org/licenses/by/2.0/) - Photo from [W_Minshull](https://www.flickr.com/photos/23950335@N07/8251484285/in/photostream/)
