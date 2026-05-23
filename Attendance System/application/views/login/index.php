<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - Attendance System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>Attendance System</h2>
            <p class="text-center mb-20">Sistema de Control de Asistencias</p>
            
            <form id="loginForm">
                <div class="form-group">
                    <label for="login-user">Usuario</label>
                    <input type="text" id="login-user" name="user" placeholder="Ingrese su usuario" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="login-password">Contraseña</label>
                    <input type="password" id="login-password" name="password" placeholder="Ingrese su contraseña" required>
                </div>
                
                <button type="submit" class="btn" id="login-btn">Iniciar Sesión</button>
            </form>
            
            <div id="login-message" class="mt-20 text-center" style="display: none;"></div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const user = document.getElementById('login-user').value.trim();
            const password = document.getElementById('login-password').value;
            const btn = document.getElementById('login-btn');
            const messageDiv = document.getElementById('login-message');
            
            if (!user || !password) {
                showMessage('Por favor complete todos los campos', 'error');
                return;
            }
            
            btn.disabled = true;
            btn.textContent = 'Procesando...';
            messageDiv.style.display = 'none';
            
            const formData = new FormData();
            formData.append('user', user);
            formData.append('password', password);
            
            fetch('<?php echo BASE_URL; ?>Login/login', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'OK') {
                    showMessage(data.msg, 'success');
                    setTimeout(() => {
                        window.location.href = '<?php echo BASE_URL; ?>Dashboard';
                    }, 1000);
                } else {
                    showMessage(data.msg, 'error');
                    btn.disabled = false;
                    btn.textContent = 'Iniciar Sesión';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error al procesar la solicitud', 'error');
                btn.disabled = false;
                btn.textContent = 'Iniciar Sesión';
            });
        });
        
        function showMessage(message, type) {
            const messageDiv = document.getElementById('login-message');
            messageDiv.textContent = message;
            messageDiv.style.display = 'block';
            messageDiv.style.color = type === 'success' ? '#28a745' : '#dc3545';
            messageDiv.style.fontWeight = 'bold';
        }
        
        document.getElementById('login-password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>
