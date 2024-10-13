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
     * Generate the Orion TypeScript model using a template.
     *
     * @param string $controller
     * @param string $table
     */
    protected function generateOrionModel(string $controller, string $table)
    {
        $modelName = Str::replaceLast('Controller', '', $controller);

        // 1. Load the template file
        $templatePath = base_path('resources/templates/ts_model.template');
        if (!file_exists($templatePath)) {
            $this->error("Template file not found at $templatePath.");
            return;
        }
        $templateContent = file_get_contents($templatePath);

        // 2. Generate the columns string
        $columns = Schema::getColumnListing($table);
        $columnsString = '';
        foreach ($columns as $column) {
            $type = $this->mapColumnType($table, $column); // Map to TypeScript types
            $columnsString .= "    $column: $type;\n";
        }

        // 3. Replace placeholders in the template
        $replacements = [
            '{{ modelName }}' => $modelName,
            '{{ columns }}' => rtrim($columnsString),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $templateContent);

        // 4. Write the output file
        $outputPath = base_path("resources/js/models/$modelName.ts");
        file_put_contents($outputPath, $content);
    }

    /**
     * Map database column types to TypeScript types.
     *
     * @param string $table
     * @param string $column
     * @return string
     */
    protected function mapColumnType(string $table, string $column): string
    {
        $type = Schema::getColumnType($table, $column);

        switch ($type) {
            case 'integer':
            case 'bigint':
            case 'smallint':
            case 'tinyint':
            case 'float':
            case 'double':
            case 'decimal':
                return 'number';
            case 'boolean':
                return 'boolean';
            case 'json':
                return 'any';
            case 'datetime':
            case 'timestamp':
            case 'date':
                return 'Date';
            default:
                return 'string';
        }
    }
}
