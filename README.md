# oc-trait-utils
Util trait for OctoberCMS

## Installation

Package requires **PHP 7.2+** and works with **OctoberCMS**.

Require the package in your `composer.json`:

```
    "require": {
        ...
        "alvaro-canepa/oc-trait-utils": "~1.0",
    },
```

## Usage example

```php
    class myModel extend Model {
        use PlanetaDelEste\Traits\Utils;

        ...

        /**
         * @param \October\Rain\Database\Builder|static $query
         * @param array                                 $data
         *
         * @return \October\Rain\Database\Builder|static
         * @throws \Exception
         */
        public function scopeFrontend($query, $data)
        {
            $columns = $this->getFullTextIndexFields();

            // Search by columns in $data array
            foreach ($data as $column => $value) {
                if (in_array($column, $columns)) {
                    $query->where($column, 'LIKE', "%{$value}%");
                }
            }

            // Search in all text columns
            if ($q = array_get($data, 'query')) {
                foreach ($columns as $column) {
                    $query->where($column, 'LIKE', "%{$q}%", 'or');
                }
            }

            return $query;
        }
    }

    $myModel = myModel::find(1);

    // Methods

    /*
        * Return array with all model columns.
        *      Example: ['id', 'created_at', 'updated_at', 'name', 'description']
        */
    $myModel->getTableColumns();

    /*
        *  Return array with all text ('VARCHAR', 'TEXT', 'CHAR') columns.
        *      Example: ['name', 'description']
        */
    $myModel->getFullTextIndexFields();

```