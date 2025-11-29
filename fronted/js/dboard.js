/**
 * DASHBOARD.JS - Lógica completa del sistema de tickets
 * Incluye: Autenticación, Tickets, Usuarios, Comentarios
 */

// ========================================
// CONFIGURACIÓN
// ========================================
const API_CONFIG = {
    USUARIOS_URL: 'http://127.0.0.1:8000',
    TICKETS_URL: 'http://127.0.0.1:8001'
};

// ========================================
// VERIFICAR AUTENTICACIÓN
// ========================================
const authToken = sessionStorage.getItem('auth_token');
const userData = JSON.parse(sessionStorage.getItem('user_data') || 'null');

if (!authToken || !userData) {
    // No hay sesión, redirigir al login
    window.location.href = 'index.html';
}

// ========================================
// FUNCIONES DE API
// ========================================
const API = {
    /**
     * Hacer petición GET
     */
    async get(url) {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Authorization': authToken,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.status === 401) {
            cerrarSesion();
            return;
        }
        
        return await response.json();
    },
    
    /**
     * Hacer petición POST
     */
    async post(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Authorization': authToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (response.status === 401) {
            cerrarSesion();
            return;
        }
        
        return await response.json();
    },
    
    /**
     * Hacer petición PUT
     */
    async put(url, data) {
        const response = await fetch(url, {
            method: 'PUT',
            headers: {
                'Authorization': authToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (response.status === 401) {
            cerrarSesion();
            return;
        }
        
        return await response.json();
    },
    
    /**
     * Hacer petición DELETE
     */
    async delete(url) {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Authorization': authToken,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.status === 401) {
            cerrarSesion();
            return;
        }
        
        return await response.json();
    }
};

// ========================================
// GESTIÓN DE AUTENTICACIÓN
// ========================================

/**
 * Cerrar sesión
 */
async function cerrarSesion() {
    try {
        // Llamar al endpoint de logout
        await fetch(`${API_CONFIG.USUARIOS_URL}/usuarios/logout`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ token: authToken })
        });
    } catch (error) {
        console.error('Error al cerrar sesión:', error);
    }
    
    // Limpiar sessionStorage
    sessionStorage.removeItem('auth_token');
    sessionStorage.removeItem('user_data');
    
    // Redirigir al login
    window.location.href = 'index.html';
}

// ========================================
// GESTIÓN DE TICKETS
// ========================================
const Tickets = {
    /**
     * Cargar lista de tickets
     */
    async cargar(filtros = {}) {
        const container = document.getElementById('ticketsList');
        container.innerHTML = '<div class="loading">Cargando tickets</div>';
        
        try {
            let url = `${API_CONFIG.TICKETS_URL}/tickets`;
            
            // Agregar filtros si existen
            if (filtros.estado) {
                url += `?estado=${filtros.estado}`;
            }
            
            const tickets = await API.get(url);
            
            if (!tickets || tickets.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <h3>📭 No hay tickets</h3>
                        <p>No se encontraron tickets con los filtros aplicados.</p>
                    </div>
                `;
                return;
            }
            
            // Renderizar tickets
            container.innerHTML = tickets.map(ticket => `
                <div class="ticket-card" onclick="Tickets.verDetalle(${ticket.id})">
                    <div class="ticket-card-header">
                        <div class="ticket-card-title">
                            <h3>${ticket.titulo}</h3>
                            <div class="ticket-id">Ticket #${ticket.id}</div>
                        </div>
                        <span class="ticket-estado estado-${ticket.estado}">
                            ${ticket.estado.replace('_', ' ')}
                        </span>
                    </div>
                    <div class="ticket-card-body">
                        ${ticket.descripcion.substring(0, 150)}${ticket.descripcion.length > 150 ? '...' : ''}
                    </div>
                    <div class="ticket-card-footer">
                        <span>👤 ${ticket.gestor ? ticket.gestor.name : 'Sin asignar'}</span>
                        <span>🔧 ${ticket.admin ? ticket.admin.name : 'Sin asignar'}</span>
                    </div>
                </div>
            `).join('');
            
        } catch (error) {
            console.error('Error al cargar tickets:', error);
            container.innerHTML = `
                <div class="alert alert-error">
                    Error al cargar los tickets. Por favor, intenta de nuevo.
                </div>
            `;
        }
    },
    
    /**
     * Ver detalle de un ticket
     */
    async verDetalle(ticketId) {
        const modal = document.getElementById('modalTicket');
        const content = document.getElementById('ticketDetalleContent');
        
        content.innerHTML = '<div class="loading">Cargando detalle</div>';
        abrirModal('modalTicket');
        
        try {
            const ticket = await API.get(`${API_CONFIG.TICKETS_URL}/tickets/${ticketId}`);
            const isAdmin = userData.role === 'admin';
            
            content.innerHTML = `
                <div class="ticket-detail-header">
                    <h3 class="ticket-detail-title">${ticket.titulo}</h3>
                    <div class="ticket-meta">
                        <span>🎫 Ticket #${ticket.id}</span>
                        <span class="ticket-estado estado-${ticket.estado}">
                            ${ticket.estado.replace('_', ' ')}
                        </span>
                    </div>
                    <div class="ticket-meta">
                        <span>👤 Creado por: ${ticket.gestor ? ticket.gestor.name : 'N/A'}</span>
                        <span>🔧 Asignado a: ${ticket.admin ? ticket.admin.name : 'Sin asignar'}</span>
                        <span>📅 ${new Date(ticket.created_at).toLocaleString('es-CO')}</span>
                    </div>
                </div>
                
                <div class="ticket-description">
                    <h4>📝 Descripción</h4>
                    <p>${ticket.descripcion}</p>
                </div>
                
                ${isAdmin ? `
                <div class="admin-actions">
                    <h4>🔧 Acciones de Administrador</h4>
                    
                    <div class="action-row">
                        <label>Estado:</label>
                        <select id="cambiarEstado" class="form-select">
                            <option value="abierto" ${ticket.estado === 'abierto' ? 'selected' : ''}>Abierto</option>
                            <option value="en_progreso" ${ticket.estado === 'en_progreso' ? 'selected' : ''}>En Progreso</option>
                            <option value="resuelto" ${ticket.estado === 'resuelto' ? 'selected' : ''}>Resuelto</option>
                            <option value="cerrado" ${ticket.estado === 'cerrado' ? 'selected' : ''}>Cerrado</option>
                        </select>
                        <button onclick="Tickets.cambiarEstado(${ticket.id})" class="btn btn-primary btn-sm">
                            Actualizar
                        </button>
                    </div>
                    
                    <div class="action-row">
                        <label>Asignar Admin (ID):</label>
                        <input type="number" id="asignarAdmin" placeholder="ID del admin" class="form-input">
                        <button onclick="Tickets.asignar(${ticket.id})" class="btn btn-success btn-sm">
                            Asignar
                        </button>
                    </div>
                </div>
                ` : ''}
                
                <div class="agregar-comentario">
                    <h4>💬 Agregar Comentario</h4>
                    <textarea id="nuevoComentario" rows="3" placeholder="Escribe tu comentario aquí..." class="form-textarea"></textarea>
                    <button onclick="Tickets.agregarComentario(${ticket.id})" class="btn btn-primary mt-2">
                        Enviar Comentario
                    </button>
                </div>
                
                <div class="comentarios-section">
                    <h4>📋 Historial de Actividad</h4>
                    <div id="historialTicket">
                        <div class="loading">Cargando historial</div>
                    </div>
                </div>
            `;
            
            // Cargar historial
            this.cargarHistorial(ticketId);
            
        } catch (error) {
            console.error('Error:', error);
            content.innerHTML = '<div class="alert alert-error">Error al cargar el ticket.</div>';
        }
    },
    
    /**
     * Cargar historial de un ticket
     */
    async cargarHistorial(ticketId) {
        const container = document.getElementById('historialTicket');
        
        try {
            const actividades = await API.get(`${API_CONFIG.TICKETS_URL}/tickets/${ticketId}/historial`);
            
            if (!actividades || actividades.length === 0) {
                container.innerHTML = '<p class="text-muted">No hay actividad registrada.</p>';
                return;
            }
            
            container.innerHTML = actividades.map(act => `
                <div class="comentario">
                    <div class="comentario-header">
                        <span class="comentario-autor">👤 ${act.usuario ? act.usuario.name : 'Usuario'}</span>
                        <span class="comentario-fecha">📅 ${new Date(act.created_at).toLocaleString('es-CO')}</span>
                    </div>
                    <div class="comentario-mensaje">${act.mensaje}</div>
                </div>
            `).join('');
            
        } catch (error) {
            console.error('Error:', error);
            container.innerHTML = '<p class="text-muted">Error al cargar el historial.</p>';
        }
    },
    
    /**
     * Cambiar estado de un ticket
     */
    async cambiarEstado(ticketId) {
        const nuevoEstado = document.getElementById('cambiarEstado').value;
        
        try {
            await API.put(`${API_CONFIG.TICKETS_URL}/tickets/${ticketId}/estado`, {
                estado: nuevoEstado
            });
            
            alert('✅ Estado actualizado exitosamente');
            this.verDetalle(ticketId);
            this.cargar();
            
        } catch (error) {
            console.error('Error:', error);
            alert('❌ Error al actualizar el estado');
        }
    },
    
    /**
     * Asignar ticket a un admin
     */
    async asignar(ticketId) {
        const adminId = document.getElementById('asignarAdmin').value;
        
        if (!adminId) {
            alert('⚠️ Por favor ingresa el ID del administrador');
            return;
        }
        
        try {
            await API.put(`${API_CONFIG.TICKETS_URL}/tickets/${ticketId}/asignar`, {
                admin_id: parseInt(adminId)
            });
            
            alert('✅ Ticket asignado exitosamente');
            this.verDetalle(ticketId);
            this.cargar();
            
        } catch (error) {
            console.error('Error:', error);
            alert('❌ Error al asignar el ticket');
        }
    },
    
    /**
     * Agregar comentario a un ticket
     */
    async agregarComentario(ticketId) {
        const mensaje = document.getElementById('nuevoComentario').value.trim();
        
        if (!mensaje) {
            alert('⚠️ Por favor escribe un comentario');
            return;
        }
        
        try {
            await API.post(`${API_CONFIG.TICKETS_URL}/tickets/${ticketId}/comentarios`, {
                mensaje: mensaje
            });
            
            document.getElementById('nuevoComentario').value = '';
            this.cargarHistorial(ticketId);
            alert('✅ Comentario agregado exitosamente');
            
        } catch (error) {
            console.error('Error:', error);
            alert('❌ Error al agregar el comentario');
        }
    },
    
    /**
     * Crear nuevo ticket
     */
    async crear(titulo, descripcion) {
        try {
            const result = await API.post(`${API_CONFIG.TICKETS_URL}/tickets`, {
                titulo: titulo,
                descripcion: descripcion
            });
            
            return result;
            
        } catch (error) {
            console.error('Error:', error);
            throw error;
        }
    }
};

// ========================================
// GESTIÓN DE USUARIOS (SOLO ADMIN)
// ========================================
const Usuarios = {
    /**
     * Cargar lista de usuarios
     */
    async cargar() {
        const container = document.getElementById('usuariosList');
        container.innerHTML = '<div class="loading">Cargando usuarios</div>';
        
        try {
            const usuarios = await API.get(`${API_CONFIG.USUARIOS_URL}/usuarios/all`);
            
            if (!usuarios || usuarios.length === 0) {
                container.innerHTML = '<div class="empty-state"><h3>No hay usuarios</h3></div>';
                return;
            }
            
            // Renderizar tabla
            container.innerHTML = `
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${usuarios.map(user => `
                                <tr>
                                    <td>${user.id}</td>
                                    <td>${user.name}</td>
                                    <td>${user.email}</td>
                                    <td>
                                        <span class="badge badge-${user.role}">${user.role}</span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button onclick="Usuarios.editar(${user.id})" class="btn btn-primary btn-sm">
                                                Editar
                                            </button>
                                            <button onclick="Usuarios.eliminar(${user.id}, '${user.name}')" class="btn btn-danger btn-sm">
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            
        } catch (error) {
            console.error('Error:', error);
            container.innerHTML = '<div class="alert alert-error">Error al cargar usuarios.</div>';
        }
    },
    
    /**
     * Editar usuario
     */
    async editar(userId) {
        try {
            const usuario = await API.get(`${API_CONFIG.USUARIOS_URL}/usuarios/${userId}`);
            
            // Llenar formulario
            document.getElementById('editUserId').value = usuario.id;
            document.getElementById('editUserName').value = usuario.name;
            document.getElementById('editUserEmail').value = usuario.email;
            document.getElementById('editUserRole').value = usuario.role;
            
            abrirModal('modalEditarUsuario');
            
        } catch (error) {
            console.error('Error:', error);
            alert('❌ Error al cargar los datos del usuario');
        }
    },
    
    /**
     * Guardar cambios del usuario
     */
    async guardar() {
        const userId = document.getElementById('editUserId').value;
        const name = document.getElementById('editUserName').value;
        const email = document.getElementById('editUserEmail').value;
        const role = document.getElementById('editUserRole').value;
        
        try {
            await API.put(`${API_CONFIG.USUARIOS_URL}/usuarios/${userId}`, {
                name: name,
                email: email,
                role: role
            });
            
            alert('✅ Usuario actualizado exitosamente');
            cerrarModal('modalEditarUsuario');
            this.cargar();
            
        } catch (error) {
            console.error('Error:', error);
            alert('❌ Error al actualizar el usuario');
        }
    },
    
    /**
     * Eliminar usuario
     */
    async eliminar(userId, userName) {
        if (!confirm(`¿Estás seguro de eliminar al usuario "${userName}"?`)) {
            return;
        }
        
        try {
            await API.delete(`${API_CONFIG.USUARIOS_URL}/usuarios/${userId}`);
            
            alert('✅ Usuario eliminado exitosamente');
            this.cargar();
            
        } catch (error) {
            console.error('Error:', error);
            alert('❌ Error al eliminar el usuario');
        }
    }
};

// ========================================
// GESTIÓN DE MODALES
// ========================================
function abrirModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function cerrarModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// ========================================
// INICIALIZACIÓN
// ========================================
document.addEventListener('DOMContentLoaded', () => {
    // Mostrar información del usuario
    document.getElementById('userName').textContent = userData.name;
    document.getElementById('userRole').textContent = userData.role;
    
    // Configurar interfaz según el rol
    const isAdmin = userData.role === 'admin';
    const isGestor = userData.role === 'gestor';
    
    if (isAdmin) {
        document.getElementById('menuUsuarios').style.display = 'block';
        document.getElementById('menuCrearTicket').style.display = 'none';
        document.getElementById('filtrosContainer').style.display = 'flex';
    } else if (isGestor) {
        document.getElementById('menuUsuarios').style.display = 'none';
        document.getElementById('menuCrearTicket').style.display = 'block';
    }
    
    // Cargar tickets al iniciar
    Tickets.cargar();
    
    // Evento: Cerrar sesión
    document.getElementById('logoutBtn').addEventListener('click', cerrarSesion);
    
    // Evento: Navegación entre secciones
    document.querySelectorAll('.nav-item').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Actualizar menú activo
            document.querySelectorAll('.nav-item').forEach(l => l.classList.remove('active'));
            e.target.classList.add('active');
            
            // Cambiar sección
            const section = e.target.getAttribute('data-section');
            document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
            document.getElementById(`section-${section}`).classList.add('active');
            
            // Cargar contenido según sección
            if (section === 'tickets') {
                Tickets.cargar();
            } else if (section === 'usuarios') {
                Usuarios.cargar();
            }
        });
    });
    
    // Evento: Filtrar tickets (admin)
    if (isAdmin) {
        document.getElementById('btnFiltrar').addEventListener('click', () => {
            const estado = document.getElementById('filtroEstado').value;
            Tickets.cargar({ estado: estado });
        });
    }
    
    // Evento: Crear ticket (gestor)
    if (isGestor) {
        document.getElementById('formCrearTicket').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const titulo = document.getElementById('ticketTitulo').value;
            const descripcion = document.getElementById('ticketDescripcion').value;
            
            try {
                await Tickets.crear(titulo, descripcion);
                
                alert('✅ Ticket creado exitosamente');
                
                // Limpiar formulario
                document.getElementById('ticketTitulo').value = '';
                document.getElementById('ticketDescripcion').value = '';
                
                // Volver a tickets
                document.querySelector('.nav-item[data-section="tickets"]').click();
                
            } catch (error) {
                alert('❌ Error al crear el ticket');
            }
        });
    }
    
    // Evento: Cerrar modales con botones
    document.querySelectorAll('.modal-close, [data-modal]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const modalId = e.target.getAttribute('data-modal') || 
                           e.target.closest('.modal').id;
            if (modalId) {
                cerrarModal(modalId);
            }
        });
    });
    
    // Evento: Cerrar modal al hacer click en el overlay
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            const modal = e.target.closest('.modal');
            if (modal) {
                cerrarModal(modal.id);
            }
        });
    });
    
    // Evento: Guardar usuario editado
    document.getElementById('formEditarUsuario').addEventListener('submit', (e) => {
        e.preventDefault();
        Usuarios.guardar();
    });
});