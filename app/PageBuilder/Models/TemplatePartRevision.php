<?php

namespace App\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class TemplatePartRevision extends Model
{
    protected $table = 'pb_template_part_revisions';

    public $timestamps = false;

    protected $fillable = ['template_part_id', 'blocks', 'label', 'created_by', 'created_at'];

    protected $casts = [
        'blocks' => 'array',
        'created_at' => 'datetime',
    ];
}
