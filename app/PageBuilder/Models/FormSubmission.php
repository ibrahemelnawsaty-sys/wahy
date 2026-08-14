<?php

namespace App\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * رسالة نموذج تواصل من صفحة محرّر — نصّ فقط (يُهرَّب عند العرض).
 */
class FormSubmission extends Model
{
    protected $table = 'pb_form_submissions';

    public $timestamps = false;

    protected $fillable = ['page_slug', 'name', 'email', 'message', 'ip', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];
}
