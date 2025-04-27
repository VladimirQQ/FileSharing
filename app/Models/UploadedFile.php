<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedFile extends Model
{
    protected $fillable = [
        'user_id',
        'original_name',
        'path',
        'size',
        'mime_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function shareLinks()
    {
        return $this->hasMany(FileShareLink::class, 'file_id');
    }
    
}
