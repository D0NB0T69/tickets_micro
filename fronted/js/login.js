/**
 * LOGIN.JS - Lógica de autenticación
 * Sistema de Tickets - Microservicios
 */

// ========================================
// CONFIGURACIÓN DE LA API
// ========================================
const API_CONFIG = {
    USUARIOS_URL: 'http://127.0.0.1:8000',
    TICKETS_URL: 'http://127.0.0.1:8001'
};

// ========================================
// VERIFICAR SI YA ESTÁ AUTENTICADO
// ========================================
const token = sessionStorage.getItem('auth_token');
if (token) {
    // Ya tiene sesión, redirigir al dashboard
    // Redirigir al dashboard
window.location.href = 'dboard.html';  // ← CAMBIO AQUÍ
}

// ========================================
// MANEJO DEL FORMULARIO DE LOGIN
// ========================================
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Obtener valores del formulario
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const errorMessage = document.getElementById('errorMessage');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    // Ocultar mensaje de error previo
    errorMessage.style.display = 'none';
    
    // Deshabilitar botón mientras se procesa
    submitBtn.disabled = true;
    submitBtn.textContent = 'Iniciando sesión...';
    
    try {
        // Llamar al endpoint de login
        const response = await fetch(`${API_CONFIG.USUARIOS_URL}/usuarios/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                password: password
            })
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            // Login exitoso
            // Guardar token en sessionStorage
            sessionStorage.setItem('auth_token', data.token);
            
            // Guardar datos del usuario
            sessionStorage.setItem('user_data', JSON.stringify(data.user));
            
            // Redirigir al dashboard
            window.location.href = 'dashboard.html';
            
        } else {
            // Mostrar error
            errorMessage.textContent = data.message || 'Credenciales inválidas';
            errorMessage.style.display = 'block';
        }
        
    } catch (error) {
        console.error('Error de conexión:', error);
        errorMessage.textContent = 'Error de conexión. Verifica que los microservicios estén en ejecución.';
        errorMessage.style.display = 'block';
        
    } finally {
        // Rehabilitar botón
        submitBtn.disabled = false;
        submitBtn.textContent = 'Iniciar Sesión';
    }
});