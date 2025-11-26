<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Reservas - INTEP</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Reutilizamos estilos del CRUD anterior */
        .layout-crud { display: flex; gap: 20px; max-width: 1200px; margin: 20px auto; }
        .panel-form { flex: 1; background: white; padding: 20px; border-radius: 8px; height: fit-content; }
        .panel-tabla { flex: 2; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px;}
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #00204a; color: white; }
        .btn-editar { background-color: #ffc107; color: black; border:none; padding:5px; cursor:pointer;}
        .btn-eliminar { background-color: #dc3545; color: white; border:none; padding:5px; cursor:pointer;}
    </style>
</head>
<body>

    <div class="barra-superior">
        <div style="display:flex; align-items:center; gap:10px;">
            <img src="img/logo.png" alt="Logo" class="logo-pequeno">
            <h2>Administración de Reservas</h2>
        </div>
        <div>
            <a href="gestion_recursos.php" class="btn" style="background:#004085; font-size: 14px;">Salones</a>
            <a href="principal.php" class="btn" style="background:#555; font-size: 14px;">Ver Calendario</a>
        </div>
    </div>

    <div class="layout-crud">
        
        <!-- FORMULARIO DE EDICIÓN -->
        <div class="panel-form">
            <h3 id="tituloForm">Editar Reserva</h3>
            <p style="font-size:12px; color:gray;">Para crear, use el Calendario. Aquí solo se editan existentes.</p>
            
            <form id="formReserva">
                <input type="hidden" id="idReserva">
                <input type="hidden" id="idUsuario"> <!-- Mantenemos el ID del usuario original -->

                <label>Salón / Laboratorio:</label>
                <select id="selectRecurso" required>
                    <option value="">Cargando...</option>
                </select>

                <label>Fecha Inicio:</label>
                <input type="datetime-local" id="fechaInicio" required>

                <label>Fecha Fin:</label>
                <input type="datetime-local" id="fechaFin" required>

                <div style="margin-top:15px;">
                    <button type="submit" class="btn" id="btnGuardar" disabled>Guardar Cambios</button>
                    <button type="button" onclick="limpiar()" style="background:#ccc; border:none; padding:10px;">Cancelar</button>
                </div>
            </form>
        </div>

        <!-- LISTA DE RESERVAS -->
        <div class="panel-tabla">
            <h3>Listado de Reservas</h3>
            <table id="tablaReservas">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Recurso</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <script>
        const API_BASE = 'http://localhost:8080/api';

        document.addEventListener('DOMContentLoaded', async () => {
            await cargarRecursos(); // Llenar el select
            cargarReservas();       // Llenar la tabla
        });

        // 1. Cargar Salones para el Select
        async function cargarRecursos() {
            const res = await fetch(`${API_BASE}/recursos`);
            const data = await res.json();
            const select = document.getElementById('selectRecurso');
            select.innerHTML = '<option value="">Seleccione...</option>';
            data.forEach(r => {
                select.innerHTML += `<option value="${r.id}">${r.nombre}</option>`;
            });
        }

        // 2. Cargar Tabla de Reservas
        async function cargarReservas() {
            const res = await fetch(`${API_BASE}/reservas`);
            const data = await res.json();
            const tbody = document.querySelector('#tablaReservas tbody');
            tbody.innerHTML = '';

            data.forEach(r => {
                // Formatear fechas para visualización
                const inicio = new Date(r.fechaInicio).toLocaleString();
                const fin = new Date(r.fechaFin).toLocaleString();

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${r.id}</td>
                    <td>${r.usuario.username}</td> 
                    <td>${r.recurso.nombre}</td>
                    <td>${inicio}</td>
                    <td>${fin}</td>
                    <td>
                        <button class="btn-editar" onclick="prepararEdicion(${r.id}, ${r.usuario.id}, ${r.recurso.id}, '${r.fechaInicio}', '${r.fechaFin}')">Editar</button>
                        <button class="btn-eliminar" onclick="eliminar(${r.id})">Borrar</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        // 3. Preparar Formulario para Editar
        window.prepararEdicion = (id, userId, recursoId, inicio, fin) => {
            document.getElementById('idReserva').value = id;
            document.getElementById('idUsuario').value = userId;
            document.getElementById('selectRecurso').value = recursoId;
            document.getElementById('fechaInicio').value = inicio; // Formato ISO funciona en datetime-local
            document.getElementById('fechaFin').value = fin;
            
            document.getElementById('btnGuardar').disabled = false;
            document.getElementById('btnGuardar').innerText = "Actualizar Reserva #" + id;
        };

        // 4. Guardar Cambios (UPDATE)
        document.getElementById('formReserva').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('idReserva').value;
            if(!id) return alert("Seleccione una reserva de la lista para editar");

            const payload = {
                usuarioId: document.getElementById('idUsuario').value,
                recursoId: document.getElementById('selectRecurso').value,
                fechaInicio: document.getElementById('fechaInicio').value,
                fechaFin: document.getElementById('fechaFin').value
            };

            const res = await fetch(`${API_BASE}/reservas/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if(res.ok) {
                alert("Reserva actualizada");
                limpiar();
                cargarReservas();
            } else {
                alert("Error al actualizar");
            }
        });

        // 5. Eliminar (DELETE)
        window.eliminar = async (id) => {
            if(!confirm("¿Seguro que desea cancelar esta reserva?")) return;

            const res = await fetch(`${API_BASE}/reservas/${id}`, { method: 'DELETE' });
            if(res.ok) {
                cargarReservas();
            } else {
                alert("Error al eliminar");
            }
        };

        function limpiar() {
            document.getElementById('formReserva').reset();
            document.getElementById('idReserva').value = '';
            document.getElementById('btnGuardar').disabled = true;
            document.getElementById('btnGuardar').innerText = "Guardar Cambios";
        }
    </script>
</body>
</html>