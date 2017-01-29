<?php

namespace Mpociot\Teamwork\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\DetectsApplicationNamespace;

class MakeTeamwork extends Command
{

    use DetectsApplicationNamespace;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:teamwork {--views : Only scaffold the teamwork views}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Teamwork scaffolding files.';
    
    protected $views = [
        'emails/invite.blade.php' => 'teamwork/emails/invite.blade.php',
        'members/list.blade.php' => 'teamwork/members/list.blade.php',
        'create.blade.php' => 'teamwork/create.blade.php',
        'edit.blade.php' => 'teamwork/edit.blade.php',
        'index.blade.php' => 'teamwork/index.blade.php',
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->createDirectories();

        $this->exportViews();
        
        if (! $this->option('views')) {
            $this->info('Installed TeamController.');
            file_put_contents(
                app_path('Http/Controllers/Teamwork/TeamController.php'),
                $this->compileControllerStub('TeamController')
            );

            $this->info('Installed TeamMemberController.');
            file_put_contents(
                app_path('Http/Controllers/Teamwork/TeamMemberController.php'),
                $this->compileControllerStub('TeamMemberController')
            );

            $this->info('Installed AuthController.');
            file_put_contents(
                app_path('Http/Controllers/Teamwork/AuthController.php'),
                $this->compileControllerStub('AuthController')
            );

            $this->info('Installed JoinTeamListener');
            file_put_contents(
                app_path('Listeners/Teamwork/JoinTeamListener.php'),
                str_replace(
                    '{{namespace}}',
                    $this->getAppNamespace(),
                    file_get_contents(__DIR__ . '/../../../stubs/listeners/JoinTeamListener.stub')
                )
            );

            $this->info('Updated Routes File.');
            file_put_contents(
               // app_path('Http/routes.php'),
               base_path('routes/web.php'),
                file_get_contents(__DIR__.'/../../../stubs/routes.stub'),
                FILE_APPEND
            );
        }
        $this->comment('Teamwork scaffolding generated successfully!');
    }

    /**
     * Create the directories for the files.
     *
     * @return void
     */
    protected function createDirectories()
    {
        if (! is_dir(app_path('Http/Controllers/Teamwork'))) {
            mkdir(app_path('Http/Controllers/Teamwork'), 0755, true);
        }
        if (! is_dir(app_path('Listeners/Teamwork'))) {
            mkdir(app_path('Listeners/Teamwork'), 0755, true);
        }
        if (! is_dir(base_path('resources/views/teamwork'))) {
            mkdir(base_path('resources/views/teamwork'), 0755, true);
        }
        if (! is_dir(base_path('resources/views/teamwork/emails'))) {
            mkdir(base_path('resources/views/teamwork/emails'), 0755, true);
        }
        if (! is_dir(base_path('resources/views/teamwork/members'))) {
            mkdir(base_path('resources/views/teamwork/members'), 0755, true);
        }
    }

    /**
     * Export the authentication views.
     *
     * @return void
     */
    protected function exportViews()
    {
        foreach ($this->views as $key => $value) {
            $path = base_path('resources/views/'.$value);
            $this->line('<info>Created View:</info> '.$path);
            copy(__DIR__.'/../../../stubs/views/'.$key, $path);
        }
    }

    /**
     * Compiles the HTTP controller stubs.
     *
     * @param $stubName
     * @return string
     */
    protected function compileControllerStub($stubName)
    {
        return str_replace(
            '{{namespace}}',
            $this->getAppNamespace(),
            file_get_contents(__DIR__.'/../../../stubs/controllers/'.$stubName.'.stub')
        );
    }
}
