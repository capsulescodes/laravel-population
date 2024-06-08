<?php

namespace CapsulesCodes\Population\Tests\App\Models\New;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Foo extends Model
{
    use HasFactory;


    protected $table = 'foo';

    protected $fillable = [ 'foo', 'bar', 'qux' ];
}
