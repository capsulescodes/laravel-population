<?php

namespace CapsulesCodes\Population\Tests\App\Models\New;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Quux extends Model
{
    use HasFactory;


    protected $connection = 'two';
    protected $table = 'quux';
    protected $fillable = [ 'quux', 'grault', 'waldo' ];
}
