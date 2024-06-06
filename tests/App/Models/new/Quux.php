<?php

namespace CapsulesCodes\Population\Tests\App\Models\New;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Quux extends Model
{
    use HasFactory;


    protected $connection = 'two';

    protected $table = 'quux';

    protected $fillable = [ 'quux', 'grault', 'waldo' ];
}
