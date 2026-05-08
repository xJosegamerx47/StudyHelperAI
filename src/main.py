from agent import StudyHelperAgent


def format_hours(hours):
    if hours == 1:
        return "1 hora"
    return f"{hours} horas"


def print_state(state, title):
    print("\n" + title)
    print("-" * 100)

    for item in state:
        print(
            f"- {item['subject']} | Tema: {item['topic']} | "
            f"Calificacion: {item['grade']} | Progreso: {item['progress']}% | "
            f"Dias para examen: {item['days_to_exam']} | Impacto: {item['impact']} | "
            f"Carga tareas: {item['task_load']} | Dificultad subjetiva: {item['subjective_difficulty']} | "
            f"Dias disponibles: {item['available_days']} | Estilo: {item['learning_style']}"
        )


def print_profile(result):
    print("\nPerfil de decision utilizado:")
    print("-" * 100)
    print(f"Perfil: {result['profile']}")

    print("\nPesos activos:")
    for factor, weight in result["weights"].items():
        print(f"- {factor}: {weight}")


def print_ranking(ranked_actions):
    print("\nRanking de prioridades calculado por el agente:")
    print("-" * 100)

    for index, action in enumerate(ranked_actions, start=1):
        print(
            f"{index}. {action['subject']} - {action['topic']} | "
            f"Puntaje: {action['score']:.3f} | "
            f"Dificultad: {action['difficulty']:.3f} | "
            f"Impacto: {action['impact']:.3f} | "
            f"Urgencia: {action['urgency']:.3f} | "
            f"Carga: {action['task_load']:.3f} | "
            f"Dificultad subjetiva: {action['subjective_difficulty']:.3f}"
        )


def print_study_plan(study_plan):
    print("\nPlan de estudio generado:")
    print("-" * 100)

    for index, item in enumerate(study_plan, start=1):
        print(
            f"{index}. {item['subject']} - {item['topic']}\n"
            f"   Prioridad: {item['priority']}\n"
            f"   Tiempo asignado: {format_hours(item['assigned_hours'])}\n"
            f"   Actividad recomendada: {item['activity']}\n"
            f"   Recurso recomendado: {item['resource']}\n"
            f"   Puntaje: {item['score']:.3f}"
        )


def print_metrics(metrics):
    print("\nMetricas iniciales del agente:")
    print("-" * 100)

    print(f"Puntaje promedio evaluado: {metrics['average_score']:.3f}")
    print(f"Mejor puntaje encontrado: {metrics['best_score']:.3f}")
    print(f"Diferencia entre primera y segunda opcion: {metrics['score_difference']:.3f}")
    print(f"Horas asignadas: {metrics['assigned_hours']}")
    print(f"Horas disponibles: {metrics['available_hours']}")
    print(f"Uso del tiempo disponible: {metrics['time_usage'] * 100:.1f}%")
    print(f"Cantidad de temas de prioridad alta en el plan: {metrics['high_priority_count']}")


def main():
    data_path = "data/student_data.csv"
    total_available_hours = 5

    # Puedes cambiar el perfil a:
    # "balanced", "urgent" o "low_performance"
    profile = "balanced"

    agent = StudyHelperAgent(
        data_path=data_path,
        total_available_hours=total_available_hours,
        profile=profile
    )

    state = agent.load_state()
    result = agent.decide(state)

    print("=" * 100)
    print("StudyHelperAI - Agente inteligente de recomendacion de estudio")
    print("=" * 100)

    print_state(state, "Estado inicial recibido por el agente")
    print_profile(result)

    print("\nFormula utilizada:")
    print(
        "f(s,a) = w1(dificultad) + w2(impacto) + w3(urgencia) "
        "+ w4(carga_tareas) + w5(dificultad_subjetiva)"
    )

    print_ranking(result["ranked_actions"])
    print_study_plan(result["study_plan"])
    print_state(result["updated_state"], "Estado actualizado despues de aplicar el plan")
    print_metrics(result["metrics"])

    print("\nConclusion de la ejecucion:")
    print(
        "El agente evaluo todas las acciones posibles, uso un perfil de pesos configurable, "
        "considero nuevas variables del estado, genero un ranking de prioridades, "
        "distribuyo el tiempo disponible y propuso actividades y recursos de estudio."
    )


if __name__ == "__main__":
    main()