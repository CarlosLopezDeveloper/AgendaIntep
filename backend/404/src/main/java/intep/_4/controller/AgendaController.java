package intep._4.controller;


import intep._4.model.Recurso;
import intep._4.model.Reserva;
import intep._4.model.Usuario;
import intep._4.repository.RecursoRepository;
import intep._4.repository.ReservaRepository;
import intep._4.repository.UsuarioRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.time.LocalDateTime;
import java.util.List;
import java.util.Map;
import java.util.Optional;

@RestController // Indica que esta clase maneja peticiones REST (JSON)
@RequestMapping("/api") // Todas las rutas empezarán por /api (ej: /api/login)
@CrossOrigin(origins = "*") // <--- VITAL: Permite que PHP (localhost) llame a Java
public class AgendaController {

    // Inyección de dependencias (Conectamos con la BD)
    @Autowired
    private UsuarioRepository usuarioRepository;

    @Autowired
    private RecursoRepository recursoRepository;

    @Autowired
    private ReservaRepository reservaRepository;

    // ---------------------------------------------------------
    // 1. ENDPOINT DE LOGIN
    // ---------------------------------------------------------
    @PostMapping("/login")
    public ResponseEntity<?> login(@RequestBody Map<String, String> credenciales) {
        String username = credenciales.get("username");
        String password = credenciales.get("password");

        // Buscamos el usuario en la BD
        Optional<Usuario> usuarioOpt = usuarioRepository.findByUsername(username);

        // Validación básica (En producción usar BCrypt para contraseñas)
        if (usuarioOpt.isPresent()) {
            Usuario usuario = usuarioOpt.get();
            if (usuario.getPassword().equals(password)) {
                // Login exitoso: Devolvemos ID y Rol
                return ResponseEntity.ok(Map.of(
                        "status", "ok",
                        "id", usuario.getId(),
                        "rol", usuario.getRol()
                ));
            }
        }

        return ResponseEntity.status(401).body(Map.of("status", "error", "mensaje", "Credenciales inválidas"));
    }

    // ---------------------------------------------------------
    // 2. ENDPOINT PARA LISTAR SALONES/LABORATORIOS
    // ---------------------------------------------------------
    @GetMapping("/recursos")
    public List<Recurso> listarRecursos() {
        return recursoRepository.findAll();
    }

    // ---------------------------------------------------------
    // 3. ENDPOINT PARA OBTENER EL CALENDARIO (RESERVAS)
    // ---------------------------------------------------------
    @GetMapping("/reservas")
    public List<Reserva> listarReservas() {
        return reservaRepository.findAll();
    }

    // ---------------------------------------------------------
    // 4. ENDPOINT PARA CREAR UNA NUEVA RESERVA
    // ---------------------------------------------------------
    @PostMapping("/reservar")
    public ResponseEntity<?> crearReserva(@RequestBody ReservaRequest request) {
        try {
            // Buscamos los objetos completos usando los IDs que envía PHP
            Usuario usuario = usuarioRepository.findById(request.getUsuarioId())
                    .orElseThrow(() -> new RuntimeException("Usuario no encontrado"));

            Recurso recurso = recursoRepository.findById(request.getRecursoId())
                    .orElseThrow(() -> new RuntimeException("Recurso no encontrado"));

            // Creamos la reserva
            Reserva nuevaReserva = new Reserva();
            nuevaReserva.setUsuario(usuario);
            nuevaReserva.setRecurso(recurso);
            nuevaReserva.setFechaInicio(request.getFechaInicio());
            nuevaReserva.setFechaFin(request.getFechaFin());

            reservaRepository.save(nuevaReserva);

            return ResponseEntity.ok(Map.of("mensaje", "Reserva creada con éxito"));

        } catch (Exception e) {
            return ResponseEntity.badRequest().body(Map.of("error", e.getMessage()));
        }
    }

    // ---------------------------------------------------------
    // CRUD DE RESERVAS (EDICIÓN Y ELIMINACIÓN)
    // ---------------------------------------------------------

    // 1. ACTUALIZAR RESERVA (PUT)
    @PutMapping("/reservas/{id}")
    public ResponseEntity<?> actualizarReserva(@PathVariable Long id, @RequestBody ReservaRequest request) {
        try {
            // Buscamos la reserva existente
            Reserva reserva = reservaRepository.findById(id)
                    .orElseThrow(() -> new RuntimeException("Reserva no encontrada"));

            // Buscamos el nuevo recurso (si se cambió)
            Recurso recurso = recursoRepository.findById(request.getRecursoId())
                    .orElseThrow(() -> new RuntimeException("Recurso no encontrado"));

            // Actualizamos los datos
            reserva.setRecurso(recurso);
            reserva.setFechaInicio(request.getFechaInicio());
            reserva.setFechaFin(request.getFechaFin());

            // Nota: No cambiamos el usuario en la edición por seguridad,
            // pero podrías hacerlo si buscas el usuarioRepository también.

            reservaRepository.save(reserva);
            return ResponseEntity.ok(Map.of("mensaje", "Reserva actualizada"));

        } catch (Exception e) {
            return ResponseEntity.badRequest().body(Map.of("error", e.getMessage()));
        }
    }

    // 2. ELIMINAR RESERVA (DELETE)
    @DeleteMapping("/reservas/{id}")
    public ResponseEntity<?> eliminarReserva(@PathVariable Long id) {
        if (reservaRepository.existsById(id)) {
            reservaRepository.deleteById(id);
            return ResponseEntity.ok(Map.of("mensaje", "Reserva eliminada"));
        } else {
            return ResponseEntity.notFound().build();
        }
    }
    // ---------------------------------------------------------
    // CRUD DE RECURSOS (SALONES Y LABORATORIOS)
    // ---------------------------------------------------------

    // 1. CREAR (POST)
    @PostMapping("/recursos")
    public Recurso crearRecurso(@RequestBody Recurso recurso) {
        return recursoRepository.save(recurso);
    }

    // 2. ACTUALIZAR (PUT)
    @PutMapping("/recursos/{id}")
    public ResponseEntity<?> actualizarRecurso(@PathVariable Long id, @RequestBody Recurso recursoDetalles) {
        return recursoRepository.findById(id).map(recurso -> {
            recurso.setNombre(recursoDetalles.getNombre());
            recurso.setTipo(recursoDetalles.getTipo());
            recurso.setCapacidad(recursoDetalles.getCapacidad());
            Recurso actualizado = recursoRepository.save(recurso);
            return ResponseEntity.ok(actualizado);
        }).orElse(ResponseEntity.notFound().build());
    }

    // 3. ELIMINAR (DELETE)
    @DeleteMapping("/recursos/{id}")
    public ResponseEntity<?> eliminarRecurso(@PathVariable Long id) {
        return recursoRepository.findById(id).map(recurso -> {
            recursoRepository.delete(recurso);
            return ResponseEntity.ok().build();
        }).orElse(ResponseEntity.notFound().build());
    }
}

/**
 * Clase auxiliar (DTO) para recibir los datos JSON desde PHP.
 * PHP envía IDs, no objetos completos, por eso necesitamos esta clase intermedia.
 * Puedes ponerla en otro archivo si prefieres, pero aquí funciona bien.
 */
class ReservaRequest {
    private Long usuarioId;
    private Long recursoId;
    private LocalDateTime fechaInicio;
    private LocalDateTime fechaFin;

    // Getters y Setters necesarios para que Spring lea el JSON
    public Long getUsuarioId() { return usuarioId; }
    public void setUsuarioId(Long usuarioId) { this.usuarioId = usuarioId; }

    public Long getRecursoId() { return recursoId; }
    public void setRecursoId(Long recursoId) { this.recursoId = recursoId; }

    public LocalDateTime getFechaInicio() { return fechaInicio; }
    public void setFechaInicio(LocalDateTime fechaInicio) { this.fechaInicio = fechaInicio; }

    public LocalDateTime getFechaFin() { return fechaFin; }
    public void setFechaFin(LocalDateTime fechaFin) { this.fechaFin = fechaFin; }
}
