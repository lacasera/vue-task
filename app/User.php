<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Monolog\Handler\IFTTTHandler;

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
    
    protected static function booted()
    {
        static::updated(function($user){
            $data = [];

            if (($user->first_name !==  $user->getOriginal('first_name')) &&  ($user->last_name !==  $user->getOriginal('last_name'))) {
                $data['name'] = $user->first_name;
            }
            
            if (($user->time_zone !==  $user->getOriginal('time_zonee'))) {
                $data['time_zone'] = $user->time_zone;
            }

            $data['email'] = $user->email;

            PendingUpdateRequest::create(['user_id' => $user->id, 'data' => json_encode($data)]); 
        });
    }
}
