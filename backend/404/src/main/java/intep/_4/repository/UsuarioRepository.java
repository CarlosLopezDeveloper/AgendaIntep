package intep._4.repository;

import intep._4.model.Usuario;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.Optional;

@Repository
public interface UsuarioRepository extends JpaRepository<Usuario, Long> {
    // Método personalizado para encontrar usuario por su username
    Optional<Usuario> findByUsername(String username);
}
