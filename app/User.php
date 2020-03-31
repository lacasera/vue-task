<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'time_zone', 'email'
    ];

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    protected static function booted()
    {
        static::updated(function($user) {
            $data = [];

            if ($user->isDirty('first_name') &&  ($user->isDirty('last_name'))) {
                $data['name'] = $user->name;
            }
            
            if ($user->isDirty('time_zone')) {
                $data['time_zone'] = $user->time_zone;
            }

            $data['email'] = $user->email;

            PendingUpdateRequest::updateOrCreate(
                ['user_id' => $user->id], 
                ['user_id' => $user->id, 'data' => json_encode($data)]
            ); 
        });
    }
}
