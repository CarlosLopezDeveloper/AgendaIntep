<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda INTEP</title>
    <link rel="stylesheet" href="style.css">
    <!-- Librería FullCalendar CSS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <style>
        #calendar { max-width: 900px; margin: 20px auto; }
        .form-reserva { background: #e3e3e3; padding: 20px; margin-top: 20px; border-radius: 8px;}
    </style>
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <img src="img/logo.png" alt="Logo INTEP" class="logo-pequeno">
            <h2>Agenda de Espacios</h2>
            <!-- Botón para ir al CRUD -->
            <a href="gestion_recursos.php" class="btn" style="background-color: #28a745; font-size: 14px;">Administrar Salones</a>
            
            <a href="gestion_reservas.php" class="btn" style="background-color: #17a2b8; font-size: 14px; margin-left:5px;">Gestionar Reservas</a>
            <a href="index.php" style="color:red;">Cerrar Sesión</a>
        </div>

        <div id='calendar'></div>

        <div class="form-reserva">
            <h3>Nueva Reserva</h3>
            <select id="recursoSelect">
                <option value="">Cargando salones...</option>
            </select>
            <input type="datetime-local" id="fechaInicio">
            <input type="datetime-local" id="fechaFin">
            <button onclick="agendar()" class="btn">Agendar</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            // 1. Cargar Calendario
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek', // Vista semanal por horas
                locale: 'es',
                events: async function(info, successCallback, failureCallback) {
                    // Traer eventos de la BD Java
                    const resp = await fetch('http://localhost:8080/api/reservas');
                    const data = await resp.json();
                    // Mapear datos de Java al formato de FullCalendar
                    const eventos = data.map(r => ({
                        title: 'Ocupado', // Podrías poner el nombre del salón
                        start: r.fechaInicio,
                        end: r.fechaFin,
                        color: '#00204a'
                    }));
                    successCallback(eventos);
                }
            });
            calendar.render();

            // 2. Cargar Lista de Salones
            const respRecursos = await fetch('http://localhost:8080/api/recursos');
            const recursos = await respRecursos.json();
            const select = document.getElementById('recursoSelect');
            select.innerHTML = '';
            recursos.forEach(r => {
                let opt = document.createElement('option');
                opt.value = r.id;
                opt.innerHTML = r.nombre + ' (' + r.tipo + ')';
                select.appendChild(opt);
            });
        });

        // 3. Función para enviar reserva
        async function agendar() {
            const recurso = document.getElementById('recursoSelect').value;
            const inicio = document.getElementById('fechaInicio').value;
            const fin = document.getElementById('fechaFin').value;

            if(!recurso || !inicio || !fin) return alert("Complete los campos");

            const payload = {
                usuarioId: 1, // Hardcodeado por simplicidad, deberías usar session storage
                recursoId: recurso,
                fechaInicio: inicio,
                fechaFin: fin
            };

            const response = await fetch('http://localhost:8080/api/reservar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if(response.ok) {
                alert("Reserva Exitosa");
                location.reload(); // Recargar para ver en calendario
            } else {
                alert("Error al reservar");
            }
        }
    </script>
</body>
</html>