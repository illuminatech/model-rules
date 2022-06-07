<?php

namespace Illuminatech\ModelRules\Test;

use Illuminate\Container\Container;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

/**
 * Base class for the test cases.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Illuminate\Contracts\Container\Container test application instance.
     */
    protected $app;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createApplication();

        $this->app->singleton('files', function () {
            return new Filesystem;
        });

        $this->app->singleton('translation.loader', function (Container $app) {
            return new FileLoader($app->make('files'), __DIR__);
        });

        $this->app->singleton('translator', function (Container $app) {
            $loader = $app->make('translation.loader');

            $trans = new Translator($loader, 'en');

            return $trans;
        });

        $db = new Manager;

        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $db->bootEloquent();
        $db->setAsGlobal();

        Model::clearBootedModels();

        $this->createSchema();
        $this->seedDatabase();
    }

    /**
     * Get a database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function getConnection()
    {
        return Model::getConnectionResolver()->connection();
    }

    /**
     * Get a schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function getSchemaBuilder()
    {
        return $this->getConnection()->getSchemaBuilder();
    }

    /**
     * Creates dummy application instance, ensuring facades functioning.
     */
    protected function createApplication()
    {
        $this->app = new Container();

        Container::setInstance($this->app);

        Facade::setFacadeApplication($this->app);
    }

    /**
     * Setup the database schema.
     *
     * @return void
     */
    protected function createSchema(): void
    {
        $this->getSchemaBuilder()->create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
        });

        $this->getSchemaBuilder()->create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->string('slug');
            $table->softDeletes();
        });
    }

    /**
     * Seeds the database schema.
     *
     * @return void
     */
    protected function seedDatabase(): void
    {
        for ($c = 1; $c <= 5; $c++) {
            $categoryId = $this->getConnection()->table('categories')->insertGetId([
                'name' => 'Category ' . $c,
            ]);

            for ($i = 1; $i <= 4; $i++) {
                $this->getConnection()->table('items')->insert([
                    'category_id' => $categoryId,
                    'name' => 'Item ' . (($c - 1) * 4 + $i),
                    'slug' => 'item-' . (($c - 1) * 4 + $i),
                ]);
            }
        }
    }
}
