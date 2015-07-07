# Teamwork (Laravel 5 Package)

[![Build Status](https://travis-ci.org/mpociot/teamwork.svg)](https://travis-ci.org/mpociot/teamwork)

Teamwork is the fastest and easiest method to add a User / Team association with Invites to your **Laravel 5** project.

**This package is still under development. Until a 1.0 Version is released, the API is likely to change.**


## Contents

- [Installation](#installation)
- [Configuration](#configuration)
    - [User relation to teams](#user-relation-to-team)
    - [Models](#models)
        - [Team](#team)
        - [User](#user)
- [Usage](#usage)
    - [Basic concepts](#basic-concepts)
    - [Get to know my team(s)](#know-my-teams)
    - [Team owner](#team-owner)
    - [Switching the current team](#switching-the-current-team)
    - [Inviting others](#inviting-others)
    - [Accepting invites](#accepting-invites)
    - [Denying invites](#denying-invites)
- [License](#license)

<a name="installation" />
## Installation

In order to install Laravel 5 Teamwork, just add 

    "mpociot/teamwork": "dev-master"

to your composer.json. Then run `composer install` or `composer update`.

Then in your `config/app.php` add 

    'Mpociot\Teamwork\TeamworkServiceProvider'
    
in the `providers` array.
    
The `Teamwork` Facade will be installed automatically within the Service Provider.

<a name="configuration"/>
## Configuration

To publish Teamwork's configuration and migration files, run the `vendor:publish` command

```bash
php artisan vendor:publish --provider="Mpociot\Teamwork\TeamworkServiceProvider"
```

This will create a `teamwork.php` in your config directory.
The default configuration should work just fine for you, but you can take a look at it, if you want to customize the table / model names Teamwork will use.

<a name="user-relation-to-team" />
### User relation to teams

Run the `migration` command, to generate all tables neede for Teamwork.


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

This will enable the relation with `Team` and add the following methods `teams()`, `currentTeam()`, `invites()`, `isOwner()`, `isOwnerOfTeam($team)`, `attachTeam($team)`, `detachTeam($team)`, `attachTeams($teams)`, `detachTeams($teams)`, `switchTeam($team)` within your `User` model.

Don't forget to dump composer autoload

```bash
composer dump-autoload
```

**And you are ready to go.**

<a name="usage" />
## Usage

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
$user->attachTeam($team); // parameter can be a Team object, array, or id

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
echo "My team has " . Auth::user()->currentTeam->users->count() . " users.";
```

The `Team` model has access to these methods:

- `invites()` &mdash; Returns a many-to-many relation to associated invitations.
- `users()` &mdash; Returns a many-to-many relation with all users associated to this team.
- `owner()` &mdash; Returns a one-to-one relation with the User model that owns this team.
- `hasUser(User $user)` &mdash; Helper function to determine if a user is a teammember

<a name="team-owner" />
### Team owner

If you need to check if the User is a team owner (regardless of the current team) use the `isOwner()` method on the User model.

```php
if( Auth::user()->isOwner() )
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
Glad we have the `UserHasTeams` trait.

```php
try {
	Auth::user()->switchTeam( $team_id );
} catch( UserNotInTeamException $e )
{
	// Given team is not allowed for the user
}
```

Just like the `isOwnerOfTeam` method, `switchTeam` accepts a Team object, array or id as a parameter.

If you try to switch

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

This method will create a `TeamworkInvite` model and return it in the callable third parameter.

This model has these attributes:

- `email` &mdash;  The email that was invited.
- `accept_token` &mdash; Unique token used to accept the invite.
- `deny_token` &mdash; Unique token used to deny the invite.

In addition to these attributes, the model has these relations:

- `user()` &mdash; one-to-one relation using the `email` as a unique identifier on the User model.
- `team()` &mdash; one-to-one relation return the Team, that invite was aiming for.

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
    
<a name="license" />
## License

Teamwork is free software distributed under the terms of the MIT license.