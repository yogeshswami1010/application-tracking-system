<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class StickyNote
 * @package App
 */
class StickyNote extends Model
{
    protected $table = 'sticky_notes';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userDetail()
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes(['active']);
    }

}
