<?php

namespace App;

use App\Scopes\CompanyScope;
use App\Project;
use App\Setting;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

// use Modules\Zoom\Observers\ZoomMeetingObserver;

class ZoomMeeting extends Model
{
    protected $table = 'zoom_meetings';
    protected $guarded = ['id'];

    protected $casts = [
        'start_date_time' => 'datetime',
        'end_date_time' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class);
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
