<?php

namespace CapsulesCodes\Population\Tests\App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use CapsulesCodes\Population\Tests\App\Database\Factories\QuuxFactory;


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
