<?php

namespace CapsulesCodes\Population\Tests\App\Models\Base;

use CapsulesCodes\Population\Tests\App\Database\Factories\QuuxFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Quux extends Model
{
    use HasFactory;


    protected $connection = 'two';

    protected $table = 'quux';

    protected $fillable = [ 'quux', 'garply', 'waldo' ];


    protected static function newFactory() : Factory
    {
        return QuuxFactory::new();
    }
}
