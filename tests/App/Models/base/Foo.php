<?php

namespace CapsulesCodes\Population\Tests\App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use CapsulesCodes\Population\Tests\App\Database\Factories\FooFactory;


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
