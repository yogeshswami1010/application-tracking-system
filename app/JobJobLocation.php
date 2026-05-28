<?php
namespace App;
use App\Job;
use App\JobLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class JobJobLocation extends Model
{
    use HasFactory;
    use Notifiable;
    protected $guarded = ['id'];

    Public function job()
    {
        return $this->belongsTo(Job::class);
    }

    Public function location()
    {
        return $this->belongsTo(JobLocation::class, 'location_id');
    }

    Public function location_data()
    {
        return $this->belongsTo(JobLocation::class, 'location_id');
    }
}
