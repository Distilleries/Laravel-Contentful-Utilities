<?php

namespace Distilleries\Contentful\Commands\Sync\Traits;

trait SyncTrait
{
    abstract public function warn($string, $verbosity = null);

    /**
     * Switch to `sync` database.
     *
     * @return void
     */
    protected function switchToSyncDb()
    {
        $this->warn('Switch to sync database');

        config([
            'database.default' => 'mysql_sync',
        ]);
    }

    /**
     * Dump `sync` database into a SQL file and return its path.
     *
     * @param  boolean  $isPreview
     * @param  string  $connector
     * @return string
     */
    protected function dumpSync(bool $isPreview, string $connector = 'mysql'): string
    {
        $path = storage_path('dumps/' . date('YmdHis') . '_sync' . ($isPreview ? '_preview' : '') . '.sql');
        $this->warn('Dump "' . basename($path) . '"...');

        $dirName = dirname($path);
        if (! is_dir($dirName)) {
            mkdir($dirName, 0777, true);
        }

        $this->dumpSql($path, $this->getConnector($isPreview, $connector));

        return realpath($path);
    }

    /**
     * Dump SQL database in given file path.
     *
     * @param  string  $path
     * @param  string  $connector
     * @return void
     */
    protected function dumpSql(string $path, string $connector)
    {
        $exec = 'export MYSQL_PWD=\'%s\'; mysqldump --add-drop-table --default-character-set=%s %s -u %s -h %s --port %s > %s';

        $command = sprintf($exec,
            addcslashes(config('database.connections.' . $connector . '.password'), "'"),
            config('database.connections.' . $connector . '.charset'),
            config('database.connections.' . $connector . '.database'),
            config('database.connections.' . $connector . '.username'),
            config('database.connections.' . $connector . '.host'),
            config('database.connections.' . $connector . '.port'),
            $path
        );
        exec($command);
    }

    protected function getConnector(bool $isPreview, string $connector = 'mysql'): string
    {
        $compiledConnector = $connector . ($isPreview ? '_preview' : '');

        if (empty(config('database.connections.' . $compiledConnector . '.username'))) {
            $compiledConnector = $connector;
        }

        return $compiledConnector;
    }


    /**
     * Put previous dump in live-preview database.
     *
     * @param  string  $path
     * @param  boolean  $isPreview
     * @return void
     */
    protected function putSync(string $path, bool $isPreview, string $connector = 'mysql')
    {
        $compiledConnector = $this->getConnector($isPreview, $connector);

        config([
            'database.default' => $compiledConnector,
        ]);

        $this->warn('Put into "' . $compiledConnector . '" database...');
        $this->putSql($path, $compiledConnector);
    }

    /**
     * Put SQL file into given database.
     *
     * @param  string  $path
     * @param  string  $connector
     * @return void
     */
    protected function putSql(string $path, string $connector)
    {
        $exec = 'export MYSQL_PWD=\'%s\'; mysql -u %s -h %s --port %s %s < %s';

        $command = sprintf($exec,
            addcslashes(config('database.connections.' . $connector . '.password'), "'"),
            config('database.connections.' . $connector . '.username'),
            config('database.connections.' . $connector . '.host'),
            config('database.connections.' . $connector . '.port'),
            config('database.connections.' . $connector . '.database'),
            $path
        );

        exec($command);
    }
}
