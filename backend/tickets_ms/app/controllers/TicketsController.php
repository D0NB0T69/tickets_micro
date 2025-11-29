<?php
namespace App\controllers;

use App\models\Ticket;
use App\models\TicketActividad;
use App\models\Usuario;
use Exception;

class TicketsController {
    
    /**
     * Obtener todos los tickets
     */
    public function getTodos($filtros = []) {
        $query = Ticket::with(['gestor', 'admin']);
        
        // Aplicar filtros
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
    
    /**
     * Obtener un ticket por ID
     */
    public function getPorId($id) {
        $ticket = Ticket::with(['gestor', 'admin'])->find($id);
        
        if (empty($ticket)) {
            throw new Exception("Ticket no encontrado", 404);
        }
        
        return $ticket->toJson();
    }
    
    /**
     * Crear nuevo ticket
     */
    public function crear($datos, $gestorId) {
        $ticket = new Ticket();
        $ticket->titulo = $datos['titulo'];
        $ticket->descripcion = $datos['descripcion'];
        $ticket->estado = 'abierto';
        $ticket->gestor_id = $gestorId;
        $ticket->save();
        
        // Registrar actividad
        $this->registrarActividad(
            $ticket->id,
            $gestorId,
            "Ticket creado",
            'sistema'
        );
        
        return $ticket->toJson();
    }
    
    /**
     * Actualizar estado del ticket
     */
    public function actualizarEstado($ticketId, $nuevoEstado, $usuarioId) {
        $ticket = Ticket::find($ticketId);
        
        if (empty($ticket)) {
            throw new Exception("Ticket no encontrado", 404);
        }
        
        $estadoAnterior = $ticket->estado;
        $ticket->estado = $nuevoEstado;
        $ticket->save();
        
        // Registrar actividad
        $this->registrarActividad(
            $ticketId,
            $usuarioId,
            "Estado cambiado de '{$estadoAnterior}' a '{$nuevoEstado}'",
            'cambio_estado'
        );
        
        return $ticket->toJson();
    }
    
    /**
     * Asignar ticket a un admin
     */
    public function asignar($ticketId, $adminId, $usuarioId) {
        $ticket = Ticket::find($ticketId);
        
        if (empty($ticket)) {
            throw new Exception("Ticket no encontrado", 404);
        }
        
        // Verificar que el admin exista
        $admin = Usuario::find($adminId);
        if (empty($admin) || $admin->role !== 'admin') {
            throw new Exception("Admin no válido", 400);
        }
        
        $ticket->admin_id = $adminId;
        $ticket->save();
        
        // Registrar actividad
        $this->registrarActividad(
            $ticketId,
            $usuarioId,
            "Ticket asignado a {$admin->name}",
            'asignacion'
        );
        
        return $ticket->toJson();
    }
    
    /**
     * Agregar comentario
     */
    public function agregarComentario($ticketId, $usuarioId, $mensaje) {
        $ticket = Ticket::find($ticketId);
        
        if (empty($ticket)) {
            throw new Exception("Ticket no encontrado", 404);
        }
        
        $this->registrarActividad(
            $ticketId,
            $usuarioId,
            $mensaje,
            'comentario'
        );
        
        return json_encode([
            'success' => true,
            'message' => 'Comentario agregado'
        ]);
    }
    
    /**
     * Obtener historial de actividades
     */
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
    
    /**
     * Registrar actividad interna
     */
    private function registrarActividad($ticketId, $usuarioId, $mensaje, $tipo) {
        $actividad = new TicketActividad();
        $actividad->ticket_id = $ticketId;
        $actividad->usuario_id = $usuarioId;
        $actividad->mensaje = $mensaje;
        $actividad->tipo = $tipo;
        $actividad->save();
    }
}