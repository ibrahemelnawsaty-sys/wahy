<?php

namespace App\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class PageRevision extends Model
{
    protected $table = 'pb_page_revisions';

    public $timestamps = false;

    protected $fillable = ['page_id', 'blocks', 'label', 'created_by', 'created_at'];

    protected $casts = [
        'blocks' => 'array',
        'created_at' => 'datetime',
    ];
}
