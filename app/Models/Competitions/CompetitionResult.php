<?php namespace App\Models\Competitions;

use App\Models\ModelWithExtraTypeInfo;

class CompetitionResult extends ModelWithExtraTypeInfo {

    public $table = 'competition_results';
    public $fillable = ['competition_id', 'entry_id', 'rank', 'content_text', 'content_html'];
    public $visible = ['id', 'competition_id', 'user_id', 'entry_id', 'rank', 'content_text', 'content_html'];
    protected $casts = [
        'rank' => 'integer',
    ];
    protected $formats = [
        'rank' => 'uin32',
    ];

}
