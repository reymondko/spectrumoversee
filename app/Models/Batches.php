<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batches extends Model
{
    protected $table = 'batches';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];

    public function batches()
    {
        return $this->hasMany('App\BatchesItems');
    }
}
