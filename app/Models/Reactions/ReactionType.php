<?php

namespace App\Models\Reactions;

use App\Models\ModelWithExtraTypeInfo;

class ReactionType extends ModelWithExtraTypeInfo
{
    protected $table = 'reaction_types';
    protected $fillable = ['id', 'name', 'is_image', 'value', 'orderindex'];
    protected $visible = ['id', 'name', 'is_image', 'value', 'orderindex'];
    protected $casts = [
        'is_image' => 'boolean',
        'orderindex' => 'integer',
    ];
    public $timestamps = false;
}
