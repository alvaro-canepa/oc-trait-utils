<?php namespace PlanetaDelEste\Traits\Utils;

use DB;

trait Utils
{
    protected $fullTextIndexTypes = ['VARCHAR', 'TEXT', 'CHAR'];

    static $columns = [];
    static $connectionName = null;
    static $tablePrefix = null;

    public static function bootUtils()
    {

    }

    /**
     * @return array
     */
    public function getTableColumns() {
        if(empty(static::$columns)) {
            static::$columns = $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
        }

        return static::$columns;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getFullTextIndexFields()
    {
        $searchableFields = $this->getTableColumns();
        $tablePrefixedName = $this->getTablePrefix().$this->getTable();
        $indexFields = [];
        foreach ($searchableFields as $searchableField) {

            $column = cache()->remember($tablePrefixedName.'.utils.'.$searchableField, 1440, function() use ($tablePrefixedName, $searchableField){
                $sql = "SHOW FIELDS FROM {$tablePrefixedName} where Field = ?";
                return DB::connection($this->getDbConnectionName())->select($sql, [$searchableField]);
            });

            if (!isset($column[0])) {
                continue;
            }

            $columnType = $column[0]->Type;
            if ($this->isFullTextSupportedColumnType($columnType)) {
                $indexFields[] = $searchableField;
            }
        }
        return $indexFields;
    }

    /**
     * @param $columnType
     *
     * @return bool
     */
    public function isFullTextSupportedColumnType($columnType)
    {
        foreach ($this->fullTextIndexTypes as $fullTextIndexType) {
            if (stripos($columnType, $fullTextIndexType) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getDbConnectionName()
    {
        if(empty(static::$connectionName)){
            static::$connectionName = $this->getConnectionName() !== null ? $this->getConnectionName() : config('database.default');
        }

        return static::$connectionName;
    }

    /**
     * @return string
     */
    public function getTablePrefix()
    {
        if(empty(static::$tablePrefix)){
            static::$tablePrefix = config("database.connections.{$this->getConnectionName()}.prefix", '');
        }

        return static::$tablePrefix;
    }
}
