<?php
namespace App\models;

use Illuminate\Database\Eloquent\Model;

class TicketActividad extends Model {
    protected $table = "ticket_actividades";
    
    public $timestamps = true;
    
    protected $fillable = [
        'ticket_id',
        'usuario_id',
        'mensaje',
        'tipo'
    ];
    
    // Relación: actividad pertenece a un ticket
    public function ticket() {
        return $this->belongsTo('App\models\Ticket', 'ticket_id');
    }
    
    // Relación: actividad pertenece a un usuario
    public function usuario() {
        return $this->belongsTo('App\models\Usuario', 'usuario_id');
    }
}