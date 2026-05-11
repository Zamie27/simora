<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    protected $table = 'categories';

    protected $fillable = ['name', 'description'];

    public function athletes(): HasMany
    {
        return $this->hasMany(User::class, 'category_id');
    }
}
