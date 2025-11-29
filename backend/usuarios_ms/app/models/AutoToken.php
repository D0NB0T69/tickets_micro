<?php
namespace App\models;

use Illuminate\Database\Eloquent\Model;

class AutoToken extends Model {
    protected $table = "auth_tokens";
    public $timestamps = false;
    
    protected $fillable = [
        'userId', 'token', 'expiresAt'
    ];

    public function user() {
        return $this->belongsTo(Usuario::class, 'userId');
    }
}