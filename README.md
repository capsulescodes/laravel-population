

# Laravel Population


Simplify database migrations and ensure consistency with your database tables effortlessly.

Laravel Population package provides a set of commands that scan your migrations and detect any disparities between them and your database tables. If differences are found, a wizard is triggered to help you migrate and seed the new tables with converted records.

<br>

Typically, your actual users table have a 'fullname' attribute, but you must have two separated attributes 'firstname' and 'lastname'.

<br>

> **WARNING**
This package serves as a proof of concept and is currently under active development. We recommend exercising caution when using it.



<br>

## Installation

```bash
composer install capsulescodes/laravel-population
```

<br>

## Usage

<br>

```bash
php artisan populate
```

The populate command will display the changes made in the migration files and ask for confirmation.

```bash
   INFO  Migration changes :

  create_users_table .......................................................................................................................... DONE

   INFO  Table 'users' has changes.

  ⇂ delete column : 'fullname' => type : string
  ⇂ create column : 'firstname' => type : string
  ⇂ create column : 'lastname' => type : string

 ┌ Do you want to proceed on populating the 'users' table? ─────┐
 │ Yes                                                          │
 └──────────────────────────────────────────────────────────────┘

 ┌ How would you like to convert the records for the column 'firstname' of type 'string'?  'fn( $attribute, $model ) => $attribute' ┐
 │ fn( $a, $b ) => explode( ' ', $b->fullname )[ 0 ]                                                                                │
 └──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

 ┌ How would you like to convert the records for the column 'lastname' of type 'string'?  'fn( $attribute, $model ) => $attribute' ┐
 │ fn( $a, $b ) => explode( ' ', $b->fullname )[ 1 ]                                                                               │
 └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

   INFO  Population succeeded.
   ```

Your `users` table has been updated and seeded with converted records. Simple.

<br>
<br>

```bash
# The populator will ask you the formula to convert existing records
'fn( $attribute, $model ) => $attribute'

# The inital representation of the parameters
$attribute = 'fullname'
$model = '$user'

# But you can decide to use any Laravel helpers instead
'fn() => fake()->firstName()'
```

<br>
<br>

If you want to rollback the latest population :

```
php artisan populate:rollback
```

<br>
<br>

The package requires the migrations to contain a `$table` property.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;


return new class extends Migration
{
    public $table = 'foo';

    public function up() : void
    {
        Schema::create( $this->table, function( Blueprint $table )
        {
            $table->id();
            $table->boolean( 'foo' );
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists( $this->table );
    }
};
```

The package has no effect if no migration has been made. Don't use it before any initial migration.

<br>

## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## Credits

- [Capsules Codes](https://github.com/capsulescodes)

## License

[MIT](https://choosealicense.com/licenses/mit/)
