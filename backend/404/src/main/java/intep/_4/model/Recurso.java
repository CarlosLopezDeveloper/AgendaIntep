package intep._4.model;


import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

@Entity
@Table(name = "recursos")
@Data
@NoArgsConstructor
@AllArgsConstructor
public class Recurso {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false)
    private String nombre; // Ej: "Laboratorio 1"

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private TipoRecurso tipo; // SALON o LABORATORIO

    private Integer capacidad;

    // Definimos el Enum aquí mismo o en otro archivo
    public enum TipoRecurso {
        SALON,
        LABORATORIO
    }
}