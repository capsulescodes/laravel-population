<?php

namespace CapsulesCodes\Population;

use CapsulesCodes\Population\Models\Schema as Data;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;


class Populator
{
    protected bool $dirty = false;


    public function isDirty() : bool
    {
        return $this->dirty;
    }

    public function process( string $database, Data $schema, Collection $formulas, Collection $records ) : void
    {
        $transforms = Collection::make();

        foreach( $formulas as $column => $formula )
        {
            if( $formula )
            {
                $variables = explode( ',', Str::of( $formula[ 1 ] )->finish( '' ) );

                $transform = Str::of( $formula[ 2 ] )->rtrim( ';' );

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

        foreach( $records as $record )
        {
            $new = $record->replicate();

            $new->setTable( $schema->code );

            foreach( $transforms as $column => $transform ) eval( $transform );

            $new->save();
        }

        Schema::connection( $database )->dropIfExists( $schema->table );

        Schema::connection( $database )->rename( $schema->code, $schema->table );

        $this->dirty = true;
    }
}
