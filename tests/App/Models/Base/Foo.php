<?php

namespace CapsulesCodes\Population\Tests\App\Models\Base;

use CapsulesCodes\Population\Tests\App\Database\Factories\FooFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Foo extends Model
{
    use HasFactory;


    protected $table = 'foo';

    protected $fillable = [ 'foo', 'baz', 'qux' ];


    protected static function newFactory() : Factory
    {
        return FooFactory::new();
    }
}
