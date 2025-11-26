<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Recursos - INTEP</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos específicos para el CRUD */
        .layout-crud { display: flex; gap: 20px; max-width: 1200px; margin: 20px auto; }
        .panel-form { flex: 1; background: white; padding: 20px; border-radius: 8px; height: fit-content; }
        .panel-tabla { flex: 2; background: white; padding: 20px; border-radius: 8px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #00204a; color: white; }
        
        .btn-editar { background-color: #ffc107; color: black; padding: 5px 10px; font-size: 14px; }
        .btn-eliminar { background-color: #dc3545; color: white; padding: 5px 10px; font-size: 14px; margin-left: 5px; }
    </style>
</head>
<body>

    <!-- Barra Superior -->
    <div class="barra-superior">
        <div style="display:flex; align-items:center; gap:10px;">
            <img src="img/logo.png" alt="Logo" class="logo-pequeno">
            <h2>Gestión de Salones y Laboratorios</h2>
        </div>
        <a href="principal.php" class="btn" style="background:#555;">Volver al Calendario</a>
    </div>

    <div class="layout-crud">
        
        <!-- FORMULARIO (SIRVE PARA CREAR Y EDITAR) -->
        <div class="panel-form">
            <h3 id="tituloFormulario">Nuevo Recurso</h3>
            <form id="recursoForm">
                <input type="hidden" id="idRecurso"> <!-- ID Oculto para editar -->
                
                <label>Nombre del Espacio:</label>
                <input type="text" id="nombre" placeholder="Ej: Aula 101" required>

                <label>Tipo:</label>
                <select id="tipo" required>
                    <option value="SALON">Salón de Clases</option>
                    <option value="LABORATORIO">Laboratorio</option>
                </select>

                <label>Capacidad (Personas):</label>
                <input type="number" id="capacidad" placeholder="Ej: 30" required>

                <div style="margin-top:15px;">
                    <button type="submit" class="btn" id="btnGuardar">Guardar</button>
                    <button type="button" onclick="limpiarFormulario()" style="background:#ccc; border:none; padding:10px;">Cancelar</button>
                </div>
            </form>
        </div>

        <!-- TABLA DE LISTADO -->
        <div class="panel-tabla">
            <h3>Listado Actual</h3>
            <table id="tablaRecursos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Capacidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Aquí se insertan los datos con JS -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const API_URL = 'http://localhost:8080/api/recursos';

        // 1. CARGAR DATOS AL INICIAR
        document.addEventListener('DOMContentLoaded', cargarTabla);

        async function cargarTabla() {
            const response = await fetch(API_URL);
            const recursos = await response.json();
            const tbody = document.querySelector('#tablaRecursos tbody');
            tbody.innerHTML = '';

            recursos.forEach(r => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${r.id}</td>
                    <td>${r.nombre}</td>
                    <td>${r.tipo}</td>
                    <td>${r.capacidad}</td>
                    <td>
                        <button class="btn btn-editar" onclick="cargarEdicion(${r.id}, '${r.nombre}', '${r.tipo}', ${r.capacidad})">Editar</button>
                        <button class="btn btn-eliminar" onclick="eliminar(${r.id})">Eliminar</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        // 2. GUARDAR (CREAR O ACTUALIZAR)
        document.getElementById('recursoForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const id = document.getElementById('idRecurso').value;
            const nombre = document.getElementById('nombre').value;
            const tipo = document.getElementById('tipo').value;
            const capacidad = document.getElementById('capacidad').value;

            const data = { nombre, tipo, capacidad };
            
            // Si hay ID, es PUT (Actualizar), si no, es POST (Crear)
            const metodo = id ? 'PUT' : 'POST';
            const url = id ? `${API_URL}/${id}` : API_URL;

            const response = await fetch(url, {
                method: metodo,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                alert('Guardado correctamente');
                limpiarFormulario();
                cargarTabla();
            } else {
                alert('Error al guardar');
            }
        });

        // 3. ELIMINAR
        async function eliminar(id) {
            if(!confirm('¿Estás seguro de borrar este salón?')) return;

            const response = await fetch(`${API_URL}/${id}`, {
                method: 'DELETE'
            });

            if(response.ok) {
                cargarTabla();
            } else {
                alert('Error al eliminar (puede que tenga reservas activas)');
            }
        }

        // 4. FUNCIONES AUXILIARES
        function cargarEdicion(id, nombre, tipo, capacidad) {
            document.getElementById('idRecurso').value = id;
            document.getElementById('nombre').value = nombre;
            document.getElementById('tipo').value = tipo;
            document.getElementById('capacidad').value = capacidad;
            
            document.getElementById('tituloFormulario').innerText = "Editar Recurso";
            document.getElementById('btnGuardar').innerText = "Actualizar";
        }

        function limpiarFormulario() {
            document.getElementById('recursoForm').reset();
            document.getElementById('idRecurso').value = '';
            document.getElementById('tituloFormulario').innerText = "Nuevo Recurso";
            document.getElementById('btnGuardar').innerText = "Guardar";
        }
    </script>
</body>
</html>