<?php
namespace App\models;

use Illuminate\Database\Eloquent\Model;

class TicketActividad extends Model {
    protected $table = "ticket_actividad";
    
    public $timestamps = true;
    
    protected $fillable = [
        'ticket_id',
        'user_id',  // ← CAMBIADO de usuario_id
        'mensaje'
    ];
    
    // Relación: actividad pertenece a un ticket
    public function ticket() {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
    
    // Relación: actividad pertenece a un usuario
    public function usuario() {
        return $this->belongsTo(Usuario::class, 'user_id');  // ← CAMBIADO
    }
}