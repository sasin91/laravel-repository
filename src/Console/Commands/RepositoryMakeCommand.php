<?php

namespace Sasin91\LaravelRepository\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * @var string
     */
    protected $contract;

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function fire()
    {
        $name = (Str::contains($this->laravel->version(), '5.4'))
        ? $this->qualifyClass($this->getNameInput())
        : $this->parseName($this->getNameInput());
        
        $this->contract($name);

        $path = $this->getPath($name);

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->type.' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        // If a model is set, just parse it and set it as the database variable.
        if ($this->option('model')) {
            $this->input->setOption('database', $this->parseModel($this->option('model')));
        }

        $this->files->put($this->getPath($this->contract), $this->buildContract($name));
        $this->files->put($path, $this->buildClass($name));

        $this->info($this->type.' created successfully.');
    }

    /**
     * Set the Contract variable.
     *
     * @param $name
     */
    protected function contract($name)
    {
        $this->contract = $name.'Contract';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('generic')) {
            return $this->stubPath('repository.database.generic.stub');
        }

        if ($this->option('database')) {
            return $this->stubPath('repository.database.stub');
        }

        return $this->stubPath('repository.plain.stub');
    }

    protected function getContractStub()
    {
        if ($this->option('generic')) {
            return $this->stubPath('contract.generic.stub');
        }
        return $this->stubPath('contract.stub');
    }

    protected function stubPath($stub)
    {
        return __DIR__.'/../../stubs/'.$stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Repositories';
    }

    protected function buildContract($name)
    {
        $stub = $this->files->get($this->getContractStub());

        $replace = [];

        if ($this->option('database')) {
            $databaseClass = $this->parseDatabase($this->option('database'));

            $replace = [
                'DummyFullDatabaseClass'    =>  $databaseClass,
                'DummyDatabaseClass'        =>  class_basename($databaseClass),
                'DummyDatabaseVariable'     =>  lcfirst(class_basename($databaseClass)),
            ];
        }

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $this->replaceNamespace($stub, $name)
                ->replaceClass($stub, $name.'Contract')
        );
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base repository import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        if ($this->option('generic') && ! $this->option('database')) {
            throw new InvalidArgumentException("Must specify a valid database to generate a generic repository for.");
        }

        $repositoryNamespace = $this->getNamespace($name);

        $replace = [];

        if ($this->option('database')) {
            $databaseClass = $this->parseDatabase($this->option('database'));

            $replace = [
                'DummyFullDatabaseClass'    =>  $databaseClass,
                'DummyDatabaseClass'        =>  class_basename($databaseClass),
                'DummyDatabaseVariable'     =>  lcfirst(class_basename($databaseClass)),
            ];
        }

        $replace['RepositoryContract'] = class_basename($this->contract);
        $replace['ContractNamespace'] = $this->contract;
        $replace["use {$repositoryNamespace}\Repositories;\n"] = '';

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Get the fully-qualified database class name.
     *
     * @param  string  $database
     * @return string
     */
    protected function parseDatabase($database)
    {
        if (! class_exists($database)) {
            throw new InvalidArgumentException("Invalid database class given {$database}.");
        }

        if (preg_match('([^A-Za-z0-9_/\\\\])', $database)) {
            throw new InvalidArgumentException('Database name contains invalid characters.');
        }

        $database = trim(str_replace('/', '\\', $database), '\\');

        if (class_exists($database)) {
            return $database;
        }

        return get_class($this->laravel->make($database));
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     */
    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        $model = trim(str_replace('/', '\\', $model), '\\');

        if (! Str::startsWith($model, $rootNamespace = $this->laravel->getNamespace())) {
            $model = $rootNamespace.$model;
        }

        return $model;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate repository for the given model.'],
            ['database', 'd', InputOption::VALUE_OPTIONAL, 'Generate repository for the given database.'],
            ['generic', 'g', InputOption::VALUE_NONE, 'Generate a generic database repository class.'],
        ];
    }
}
