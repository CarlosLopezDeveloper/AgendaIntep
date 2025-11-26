package intep._4.repository;

import intep._4.model.Recurso;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface RecursoRepository extends JpaRepository<Recurso, Long> {
    // Aquí podrías agregar métodos como findByTipo(TipoRecurso tipo);
}