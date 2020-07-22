<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchesItems extends Model
{
    protected $table = 'batches_items';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];

    public function batches()
    {
        return $this->hasOne('App\Batches');
    }
}
