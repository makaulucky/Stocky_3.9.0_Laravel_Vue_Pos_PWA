<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OauthAccessToken extends Model
{

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function oauthRefreshToken()
    {
        return $this->hasMany('\App\Models\OauthRefreshToken', 'access_token_id');
    }

}
