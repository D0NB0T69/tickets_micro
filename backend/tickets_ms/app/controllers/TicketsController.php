<?php
namespace App\controllers;

use App\models\Ticket;
use App\models\TicketActividad;
use App\models\Usuario;
use Exception;

class TicketsController {
    
    public function getTodos($filtros = []) {
        $query = Ticket::with(['gestor', 'admin']);
        
        if (isset($filtros['estado']) && !empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        
        if (isset($filtros['gestor_id'])) {
            $query->where('gestor_id', $filtros['gestor_id']);
        }
        
        if (isset($filtros['admin_id'])) {
            $query->where('admin_id', $filtros['admin_id']);
        }
        
        $tickets = $query->orderBy('created_at', 'desc')->get();
        
        if (count($tickets) == 0) {
            return null;
        }
        
        return $tickets->toJson();
    }
    
    public function getPorId($id) {
        $ticket = Ticket::with(['gestor', 'admin'])->find($id);
        
        if (empty($ticket)) {
            throw new Exception("Ticket no encontrado", 404);
        }
        
        return $ticket->toJson();
    }
    
    public function crear($datos, $gestorId) {
        $ticket = new Ticket();
        $ticket->titulo = $datos['titulo'];
        $ticket->descripcion = $datos['descripcion'];
        $ticket->estado = 'abierto';
        $ticket->gestor_id = $gestorId;
        $ticket->save();
        
        $this->registrarActividad($ticket->id, $gestorId, "Ticket creado");
        
        return $ticket->toJson();
    }
    
    public function actualizarEstado($ticketId, $nuevoEstado, $usuarioId) {
        $ticket = Ticket::find($ticketId);
        
        if (empty($ticket)) {
            throw new Exception("Ticket no encontrado", 404);
        }
        
        $estadoAnterior = $ticket->estado;
        $ticket->estado = $nuevoEstado;
        $ticket->save();
        
        $this->registrarActividad(
            $ticketId,
            $usuarioId,
            "Estado cambiado de '{$estadoAnterior}' a '{$nuevoEstado}'"
        );
        
        return $ticket->toJson();
    }
    
    public function asignar($ticketId, $adminId, $usuarioId) {
        $ticket = Ticket::find($ticketId);
        
        if (empty($ticket)) {
            throw new Exception("Ticket no encontrado", 404);
        }
        
        $admin = Usuario::find($adminId);
        if (empty($admin) || $admin->role !== 'admin') {
            throw new Exception("Admin no válido", 400);
        }
        
        $ticket->admin_id = $adminId;
        $ticket->save();
        
        $this->registrarActividad(
            $ticketId,
            $usuarioId,
            "Ticket asignado a {$admin->name}"
        );
        
        return $ticket->toJson();
    }
    
    public function agregarComentario($ticketId, $usuarioId, $mensaje) {
        $ticket = Ticket::find($ticketId);
        
        if (empty($ticket)) {
            throw new Exception("Ticket no encontrado", 404);
        }
        
        $this->registrarActividad($ticketId, $usuarioId, $mensaje);
        
        return json_encode([
            'success' => true,
            'message' => 'Comentario agregado'
        ]);
    }
    
    public function getHistorial($ticketId) {
        $actividades = TicketActividad::with('usuario')
            ->where('ticket_id', $ticketId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        if (count($actividades) == 0) {
            return null;
        }
        
        return $actividades->toJson();
    }
    
    private function registrarActividad($ticketId, $usuarioId, $mensaje) {
        $actividad = new TicketActividad();
        $actividad->ticket_id = $ticketId;
        $actividad->user_id = $usuarioId;  // ← CAMBIADO
        $actividad->mensaje = $mensaje;
        $actividad->save();
    }
}