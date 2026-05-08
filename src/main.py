from agent import StudyHelperAgent


def main():
    data_path = "data/student_data.csv"

    agent = StudyHelperAgent(data_path)

    state = agent.load_state()
    action = agent.choose_best_action(state)

    print("=" * 60)
    print("StudyHelperAI - Agente inteligente de recomendacion de estudio")
    print("=" * 60)

    print("\nEstado recibido por el agente:")
    for item in state:
        print(
            f"- {item['subject']} | Tema: {item['topic']} | "
            f"Calificacion: {item['grade']} | Progreso: {item['progress']}% | "
            f"Dias para examen: {item['days_to_exam']} | Impacto: {item['impact']}"
        )

    print("\nDecision del agente:")
    print(f"Materia recomendada: {action['subject']}")
    print(f"Tema recomendado: {action['topic']}")
    print(f"Horas recomendadas: {action['recommended_hours']}")

    print("\nValores usados en la funcion de decision:")
    print(f"Dificultad: {action['difficulty']:.3f}")
    print(f"Impacto: {action['impact']:.3f}")
    print(f"Urgencia: {action['urgency']:.3f}")
    print(f"Puntaje final f(s,a): {action['score']:.3f}")

    print("\nAccion recomendada:")
    print(action["message"])

    print("\nFormula utilizada:")
    print("f(s,a) = 0.40(dificultad) + 0.35(impacto) + 0.25(urgencia)")


if __name__ == "__main__":
    main()