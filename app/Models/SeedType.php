<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeedType extends Model
{
    protected $fillable = ['name', 'cuidados_por_nivel', 'monedas_cosecha'];

    public function trees()
    {
        return $this->hasMany(Tree::class);
    }
}
