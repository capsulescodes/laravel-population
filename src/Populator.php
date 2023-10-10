<?php

namespace CapsulesCodes\Population;

use CapsulesCodes\Population\Traits\WriteTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function Laravel\Prompts\text;


class Populator
{
    use WriteTrait;


    private bool $modified = false;


    public function isModified() : bool
    {
        return $this->modified;
    }

    protected function listen( string $uuid, Collection $dirties ) : void
    {
        foreach( $dirties as $table => $changes )
        {
            $transforms = collect( [] );

            foreach( $changes as $column => $change )
            {
                if( $change[ 'new' ] )
                {
                    $matches = $this->ask( $column );

                    $variables = explode( ',', Str::of( $matches[ 1 ] )->finish('') );

                    $transform = Str::of( $matches[ 2 ] )->rtrim( ';' );

                    $transform = $transform->replace( Str::of( $variables[ 0 ] ?? '' )->trim(), '$record[ $column ]' );

                    $transform = $transform->replace( Str::of( $variables[ 1 ] ?? '' )->trim(), '$record' );

                    $transform = $transform->prepend( '$new->offsetSet( $column, ' )->append( ');' );

                    $transforms->put( $column, $transform->value );
                }
                else
                {
                    $transforms->put( $column, '$new->offsetUnset( $column );' );
                }
            }

            foreach( $this->load( $table ) as $record )
            {
                $new = $record->replicate();

                $new->setTable( "{$table}{$uuid}" );

                foreach( $transforms as $column => $transform )
                {
                    eval( $transform );
                }

                $new->save();
            }

            Schema::dropIfExists( $table );

            Schema::rename( "{$table}{$uuid}", $table );

            $this->modified = true;
        }
    }


    protected function ask( $column ) : array
    {
        $input = text( "How would you like to change the records for the column '{$column}'?", 'fn( $value, $model ) => $value' );

        preg_match( '/^\s*fn\s*\(\s*(\$[\w\d]*\s*(?:,\s*\$[\w\d]*)?)?\s*\)\s*=>\s*(.+)\s*/', $input, $matches );

        if( Collection::make( $matches )->isEmpty() )
        {
            $this->write( Error::class, "The function did not respect the required format" );

            exit();
        }

        return $matches;
    }


    protected function load( $table = null, $input = null ) : Collection
    {
        $class = $input ?? 'App\\Models\\' . Str::studly( Str::singular( $table ) ) ;

        try
        {
            class_exists( $class );
        }
        catch( \Exception )
        {
            if( ! $input ) $this->load( null, text( "The '{$class}' model path does not exist, please provide the correct path", "App\\Models\\" ) );

            $this->write( Error::class, "The model file wasn't found" );

             exit();
        }

        return $class::all();
    }
}
