from agent import StudyHelperAgent


def print_state(state, title):
    print("\n" + title)
    print("-" * 80)

    for item in state:
        print(
            f"- {item['subject']} | Tema: {item['topic']} | "
            f"Calificacion: {item['grade']} | Progreso: {item['progress']}% | "
            f"Dias para examen: {item['days_to_exam']} | Impacto: {item['impact']}"
        )


def print_ranking(ranked_actions):
    print("\nRanking de prioridades calculado por el agente:")
    print("-" * 80)

    for index, action in enumerate(ranked_actions, start=1):
        print(
            f"{index}. {action['subject']} - {action['topic']} | "
            f"Puntaje: {action['score']:.3f} | "
            f"Dificultad: {action['difficulty']:.3f} | "
            f"Impacto: {action['impact']:.3f} | "
            f"Urgencia: {action['urgency']:.3f}"
        )


def print_study_plan(study_plan):
    print("\nPlan de estudio generado:")
    print("-" * 80)

    for index, item in enumerate(study_plan, start=1):
        print(
            f"{index}. Estudiar {item['subject']} - {item['topic']} "
            f"durante {item['assigned_hours']} horas "
            f"(puntaje: {item['score']:.3f})"
        )


def print_metrics(metrics):
    print("\nMetricas iniciales del agente:")
    print("-" * 80)

    print(f"Puntaje promedio evaluado: {metrics['average_score']:.3f}")
    print(f"Mejor puntaje encontrado: {metrics['best_score']:.3f}")
    print(f"Diferencia entre primera y segunda opcion: {metrics['score_difference']:.3f}")
    print(f"Horas asignadas: {metrics['assigned_hours']}")
    print(f"Horas disponibles: {metrics['available_hours']}")
    print(f"Uso del tiempo disponible: {metrics['time_usage'] * 100:.1f}%")


def main():
    data_path = "data/student_data.csv"
    total_available_hours = 5

    agent = StudyHelperAgent(
        data_path=data_path,
        total_available_hours=total_available_hours
    )

    state = agent.load_state()
    result = agent.decide(state)

    print("=" * 80)
    print("StudyHelperAI - Agente inteligente de recomendacion de estudio")
    print("=" * 80)

    print_state(state, "Estado inicial recibido por el agente")

    print("\nFormula utilizada:")
    print("f(s,a) = 0.40(dificultad) + 0.35(impacto) + 0.25(urgencia)")

    print_ranking(result["ranked_actions"])
    print_study_plan(result["study_plan"])
    print_state(result["updated_state"], "Estado actualizado despues de aplicar el plan")
    print_metrics(result["metrics"])

    print("\nConclusion de la ejecucion:")
    print(
        "El agente evaluo todas las acciones posibles, genero un ranking de prioridades, "
        "distribuyo el tiempo disponible y simulo la mejora del progreso despues de estudiar."
    )


if __name__ == "__main__":
    main()