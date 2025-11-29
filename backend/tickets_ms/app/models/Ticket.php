<?php
namespace App\models;

use Illuminate\Database\Eloquent\Model;

class TicketActividad extends Model {
    protected $table = "ticket_actividad";
    
    public $timestamps = true;
    
    protected $fillable = [
        'ticket_id',
        'user_id',
        'mensaje'
    ];
    
    // Relación: actividad pertenece a un ticket
    public function ticket() {
        return $this->belongsTo('App\models\Ticket', 'ticket_id');
    }
    
    // Relación: actividad pertenece a un usuario
    public function usuario() {
        return $this->belongsTo('App\models\Usuario', 'user_id');
    }
}