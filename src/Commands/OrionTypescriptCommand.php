<?php

namespace Inquid\LaravelGii\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Str;

class OrionTypescriptCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:orion-models
                            {controller : The name of the controller}
                            {--c|connection= : The name of the connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Orion SDK TypeScript models based on the Laravel model';

    /**
     * @var Repository
     */
    protected $config;

    /**
     * Create a new command instance.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        parent::__construct();

        $this->config = $config;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = $this->argument('controller');
        $modelClass = $this->getModelClassFromController($controller);

        if (!class_exists($modelClass)) {
            $this->error("Model for controller $controller does not exist.");
            return;
        }

        $modelInstance = new $modelClass;
        $connection = $this->getConnection($modelInstance);
        $schema = $this->getSchema($connection);
        $table = $modelInstance->getTable();

        $this->generateOrionModel($controller, $table);
        $this->info("Orion SDK TypeScript model generated for $controller.");
    }

    /**
     * Get the model class name from the controller name.
     *
     * @param string $controller
     * @return string
     */
    protected function getModelClassFromController(string $controller): string
    {
        $modelName = Str::replaceLast('Controller', '', $controller);
        return "App\\Models\\$modelName";
    }

    /**
     * Get the database connection for the model.
     *
     * @param $modelInstance
     * @return string
     */
    protected function getConnection($modelInstance)
    {
        return $this->option('connection') ?: $modelInstance->getConnectionName() ?: $this->config->get('database.default');
    }

    /**
     * Get the schema name for the given connection.
     *
     * @param string $connection
     * @return string
     */
    protected function getSchema(string $connection): string
    {
        return $this->config->get("database.connections.$connection.database");
    }

    /**
     * Generate the Orion TypeScript model.
     *
     * @param string $controller
     * @param string $table
     */
    protected function generateOrionModel(string $controller, string $table)
    {
        $modelName = Str::replaceLast('Controller', '', $controller);
        $content = "import {Model} from \"@tailflow/laravel-orion/lib/model\";\n\n";
        $content .= "export class $modelName extends Model<{\n";

        $columns = Schema::getColumnListing($table);
        foreach ($columns as $column) {
            $type = 'string'; // You could enhance this to detect the proper TypeScript type based on the column type.
            $content .= "    $column: $type,\n";
        }

        $content .= "}>\n{

}";

        $outputPath = base_path("resources/js/models/$modelName.ts");
        file_put_contents($outputPath, $content);
    }
}
