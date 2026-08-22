<?php

namespace App\Models\Reactions;

use Illuminate\Database\Eloquent\Model;

class ReactionType extends Model
{
    protected $table = 'reaction_types';
    protected $fillable = ['id', 'name', 'is_image', 'value', 'orderindex'];
    protected $visible = ['id', 'name', 'is_image', 'value', 'orderindex'];
    public $timestamps = false;
}
