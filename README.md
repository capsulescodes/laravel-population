
<p align="center"><img src="capsules-laravel-population-image.png" width="400px" height="265px" alt="Laravel Population" /></p>

Simplify database migrations and ensure consistency with your database tables effortlessly.

Laravel Population package provides a set of commands that parses your migrations and detects any disparities between them and your database tables. If differences are found, a wizard is triggered to help you migrate and seed the new tables with converted records.

<br>

Typically, your `users` table might have a `fullname` column, but you need two separate columns : `firstname` and `lastname`. However, your database is already full of records.

<br>

 [This article](https://capsules.codes/en/blog/fyi/en-fyi-modify-tables-and-records-with-laravel-population) provides an in-depth exploration of the package.

<br>

> [!WARNING]
> We recommend exercising caution when using this package on production.

<br>

## Installation

```bash
composer require --dev capsulescodes/laravel-population
```

<br>

## Usage

<br>

Let's say, your current `users` table have a `fullname` column, but you need two separate columns : `firstname` and `lastname`. First, modify your migration :

<br>

```diff
...
Schema::create( 'users', function( Blueprint $table )
{
    $table->id();
-    $table->string( 'fullname' );
+    $table->string( 'firstname' );
+    $table->string( 'lastname' );
} );
...
```

<br>

Now unleash the magic :

```bash
php artisan populate
```

<br>

The populate command will display the changes made in the migration files and ask for confirmation.

```bash
   INFO  Migration changes :

  create_users_table .......................................................................................................................... DONE

   INFO  Table 'users' has changes.

  ⇂ delete column : 'fullname' => type : varchar
  ⇂ create column : 'firstname' => type : varchar
  ⇂ create column : 'lastname' => type : varchar

 ┌ Do you want to proceed on populating the 'users' table? ─────┐
 │ Yes                                                          │
 └──────────────────────────────────────────────────────────────┘

 ┌ How would you like to convert the records for the column 'varchar' of type 'string'?  'fn( $attribute, $model ) => $attribute' ┐
 │ fn( $a, $b ) => explode( ' ', $b->fullname )[ 0 ]                                                                                │
 └──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

 ┌ How would you like to convert the records for the column 'lastname' of type 'varchar'?  'fn( $attribute, $model ) => $attribute' ┐
 │ fn( $a, $b ) => explode( ' ', $b->fullname )[ 1 ]                                                                               │
 └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

   INFO  Population succeeded.
   ```

Your `users` table has been updated and seeded with converted records. Simple.

<br>

```diff
App\Models\User
{
    id: 1,
-    fullname: "Louie Wolff",
+    firstname: "Louie",
+    lastname: "Wolff",
},
App\Models\User
{
    id: 2,
-    fullname: "Holly Waters",
+    firstname: "Holly",
+    lastname: "Waters",
},
App\Models\User
{
    id: 3,
-    fullname: "Colton Mueller",
+    firstname: "Colton",
+    lastname: "Mueller",
},
...
```

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

```bash
php artisan populate:rollback
```

<br>

```bash
   WARN  The rollback command will only set back the latest copy of your database(s). You'll have to modify your migrations and models manually.

   INFO  Database dump successfully reloaded.
```

<br>
<br>

## Options


```bash
php artisan populate --path={path-to-migrations-to-populate} --realpath={true|false} --database={database-name} --daptabase={database-name}
```

<br>

- Laravel Population supports SQLite, MySQL, and MariaDB.
- Laravel Population can work with multiple databases.
- Laravel Population supports both anonymous and named migrations classes.
- Laravel Population supports multiple table creation in migration files.

<br>

## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.
In order to run MySQL tests, credentials have to be configured in the intended TestCases.

## Credits

- [Capsules Codes](https://github.com/capsulescodes)

## License

[MIT](https://choosealicense.com/licenses/mit/)
