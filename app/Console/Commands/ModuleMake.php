<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModuleMake extends Command
{
    protected  $files;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name}
                                        {--all : All items}
                                        {--migration : Only migration}
                                        {--view : Only View}
                                        {--model : Only View}
                                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if($this->option('all')) {
            $this->input->setOption("migration", true);
            $this->input->setOption("view", true);
            $this->input->setOption("model", true);
        }

        if($this->option('model')) {
            $this->createModel();
        }
        if($this->option('migration')) {
            $this->createMigration();
        }

        $this->creatController();

        if($this->option('view')) {
            $this->createView();
        }


    }

    private function createModel()
    {
        try {
            $model = Str::singular(class_basename($this->argument('name')));
            $this->call('make:model', [
                'name' => "App\\Modules\\".trim($this->argument('name'))."\\Models\\".$model
            ]);
        }catch(\Exception $e) {
            $e->getMessage();
        }

    }

    private function createMigration()
    {
        $table = Str::plural(Str::snake(class_basename($this->argument('name'))));

        try {
            $this->call('make:migration', [
                'name' => "create_".$table."_table",
                "--create" => $table
            ]);

        }catch(\Exception $e) {
            $e->getMessage();
        }
    }

    private function creatController()
    {
        $controller = Str::studly(class_basename($this->argument('name')));
        $modelName = Str::singular(Str::studly(class_basename($this->argument('name'))));
        
        $path = $this->getControllerPath($this->argument('name'));

        $this->makeDirectory($path);

        $stub = $this->files->get(base_path('resources/stubs/controller.model.stub'));

        $stub = str_replace(
            [
                'DummyNamespace',
                'DummyRootNamespace',
                'DummyClass',
                'DummyFullModelClass',
                'DummyModelClass',
                'DummyModelVariable',
            ],
            [
                "App\\Modules\\".trim($this->argument('name'))."\\Controllers",
                $this->laravel->getNamespace(),
                $controller.'Controller',
                "App\\Modules\\".trim($this->argument('name'))."\\Models\\{$modelName}",
                $modelName,
                lcfirst(($modelName))
            ],
            $stub
        );

        $this->files->put($path, $stub);
        $this->info("Controller created");

        $this->createRoutes($controller, $modelName);

    }

    private function getControllerPath($argument)
    {
        $controller = Str::studly(class_basename($argument));
        return $this->laravel['path'].'/Modules/'.str_replace('\\', '/', $argument)."/Controllers/"."{$controller}Controller.php";

    }

    private function makeDirectory($path)
    {
        $this->files->makeDirectory(dirname($path),0777, true, true);
    }

    private function createRoutes($controller, $modelName)
    {
        $routePath = $this->getRoutesPath($this->argument('name'));

        if ($this->alreadyExists($routePath)) {
            $this->error('Routes already exists!');
        } else {

            $this->makeDirectory($routePath);

            $stub = $this->files->get(base_path('resources/stubs/routes.web.stub'));

            $stub = str_replace(
                [
                    'DummyClass',
                    'DummyRoutePrefix',
                    'DummyModelVariable',
                ],
                [
                    $controller.'Controller',
                    Str::plural(Str::snake(lcfirst($modelName), '-')),
                    lcfirst($modelName)
                ],
                $stub
            );

            $this->files->put($routePath, $stub);
            $this->info('Routes created successfully.');
        }
    }

    private function getRoutesPath($argument)
    {
        return $this->laravel['path'].'/Modules/'.str_replace('\\', '/', $argument)."/Routes/web.php";

    }

    private function alreadyExists($routePath)
    {
        return $this->files->exists($routePath);
    }

    /**
     * @param $name
     * @return array
     */
    protected function getViewPath($name)
    {

        $arrFiles = collect([
            'create',
            'edit',
            'index',
            'show',
        ]);

        //str_replace('\\', '/', $name)
        $paths = $arrFiles->map(function($item) use ($name){
            return base_path('resources/views/'.str_replace('\\', '/', $name).'/'.$item.".blade.php");
        });

        return $paths;
    }

    protected function createView()
    {
        $paths = $this->getViewPath($this->argument('name'));

        foreach ($paths as $path) {
            $view = Str::studly(class_basename($this->argument('name')));

            if ($this->alreadyExists($path)) {
                $this->error('View already exists!');
            } else {
                $this->makeDirectory($path);

                $stub = $this->files->get(base_path('resources/stubs/view.stub'));

                $stub = str_replace(
                    [
                        '',
                    ],
                    [
                    ],
                    $stub
                );

                $this->files->put($path, $stub);
                $this->info('View created successfully.');
            }
        }
    }

}
