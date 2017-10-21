<?php

namespace Glumen\Console\Commands;

use Glumen\Console\Generators\ValidatorGenerator;
use Exception;
use Illuminate\Console\Command;

class ValidatorMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'glumen:validator {validator} {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new validator';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $generator = new ValidatorGenerator();
        $name      = $this->argument('validator');
        $domain    = $this->argument('domain');
        try {
            $validator = $generator->generate($name, $domain);
            $this->info('Validator class created successfully.'.
                "\n".
                "\n".
                'Find it at <comment>'.$validator.'</comment>'."\n"
            );
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}