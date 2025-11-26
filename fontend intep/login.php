<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login INTEP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <img src="img/logo.png" alt="Logo INTEP" class="logo-intep" style="width: 100px;">
        <h2>Iniciar Sesión</h2>
        <form id="loginForm">
            <input type="text" id="username" placeholder="Usuario" required><br>
            <input type="password" id="password" placeholder="Contraseña" required><br>
            <button type="submit" class="btn">Entrar</button>
        </form>
        <p id="mensaje" style="color:red;"></p>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const user = document.getElementById('username').value;
            const pass = document.getElementById('password').value;

            try {
                // Petición al Backend Spring Boot
                const response = await fetch('http://localhost:8080/api/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: user, password: pass })
                });

                if (response.ok) {
                    window.location.href = 'principal.php';
                } else {
                    document.getElementById('mensaje').innerText = "Credenciales incorrectas";
                }
            } catch (error) {
                console.error(error);
                document.getElementById('mensaje').innerText = "Error de conexión con el servidor";
            }
        });
    </script>
</body>
</html>