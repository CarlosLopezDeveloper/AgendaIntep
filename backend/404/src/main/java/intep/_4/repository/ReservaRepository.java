package intep._4.repository;



import  intep._4.model.Reserva;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface ReservaRepository extends JpaRepository<Reserva, Long> {
    // Ejemplo: Buscar reservas de un recurso específico
    // List<Reserva> findByRecursoId(Long recursoId);
}
