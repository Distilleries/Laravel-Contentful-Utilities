<?php

namespace Distilleries\Contentful\Commands\Sync\Traits;

trait SyncTrait
{
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
     * @return string
     */
    protected function dumpSync(bool $isPreview, string $connector='mysql') : string
    {
        $path = storage_path('dumps/' . date('YmdHis') . '_sync' . ($isPreview ? '_preview' : '') . '.sql');
        $this->warn('Dump "' . basename($path) . '"...');

        $dirName = dirname($path);
        if (! is_dir($dirName)) {
            mkdir($dirName, 0777, true);
        }

        $this->dumpSql($path, $connector);

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
        $exec = 'export MYSQL_PWD=%s; mysqldump --add-drop-table --default-character-set=%s %s -u %s -h %s --port %s > %s';

        $command = sprintf($exec,
            config('database.connections.' . $connector . '.password'),
            config('database.connections.' . $connector . '.charset'),
            config('database.connections.' . $connector . '.database'),
            config('database.connections.' . $connector . '.username'),
            config('database.connections.' . $connector . '.host'),
            config('database.connections.' . $connector . '.port'),
            $path
        );
        exec($command);
    }

    /**
     * Put previous dump in live-preview database.
     *
     * @param  string  $path
     * @param  boolean $isPreview
     * @return void
     */
    protected function putSync(string $path, bool $isPreview, string $connector='mysql')
    {
        config([
            'database.default' => 'mysql' . ($isPreview ? '_preview' : ''),
        ]);

        $this->warn('Put into "' . $connector . '" database...');
        $this->putSql($path, $connector);
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
        $exec = 'export MYSQL_PWD=%s; mysql -u %s -h %s --port %s %s < %s';

        $command = sprintf($exec,
            config('database.connections.' . $connector . '.password'),
            config('database.connections.' . $connector . '.username'),
            config('database.connections.' . $connector . '.host'),
            config('database.connections.' . $connector . '.port'),
            config('database.connections.' . $connector . '.database'),
            $path
        );

        exec($command);
    }
}
