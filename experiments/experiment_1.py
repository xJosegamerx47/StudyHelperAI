import sys
import os
from contextlib import redirect_stdout

sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "src")))

from agent import StudyHelperAgent


RESULTS_PATH = os.path.abspath(
    os.path.join(os.path.dirname(__file__), "..", "results", "experiment_results.txt")
)


def format_hours(hours):
    if hours == 1:
        return "1 hora"
    return f"{hours} horas"


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
    print("\nRanking de prioridades:")
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
    print("\nPlan de estudio:")
    print("-" * 80)

    for index, item in enumerate(study_plan, start=1):
        print(
            f"{index}. Estudiar {item['subject']} - {item['topic']} "
            f"durante {format_hours(item['assigned_hours'])} "
            f"(puntaje: {item['score']:.3f})"
        )


def print_metrics(metrics):
    print("\nMetricas del escenario:")
    print("-" * 80)

    print(f"Puntaje promedio evaluado: {metrics['average_score']:.3f}")
    print(f"Mejor puntaje encontrado: {metrics['best_score']:.3f}")
    print(f"Diferencia entre primera y segunda opcion: {metrics['score_difference']:.3f}")
    print(f"Horas asignadas: {metrics['assigned_hours']}")
    print(f"Horas disponibles: {metrics['available_hours']}")
    print(f"Uso del tiempo disponible: {metrics['time_usage'] * 100:.1f}%")


def run_scenario(name, state, total_available_hours=5):
    agent = StudyHelperAgent(
        data_path=None,
        total_available_hours=total_available_hours
    )

    result = agent.decide(state)

    print("=" * 90)
    print(f"ESCENARIO: {name}")
    print("=" * 90)

    print_state(state, "Estado inicial")
    print_ranking(result["ranked_actions"])
    print_study_plan(result["study_plan"])
    print_state(result["updated_state"], "Estado actualizado despues del plan")
    print_metrics(result["metrics"])

    print("\nInterpretacion:")
    print(
        "El agente comparo todas las acciones posibles, eligio las de mayor prioridad "
        "y distribuyo el tiempo disponible de acuerdo con la funcion heuristica."
    )
    print()


def run_all_experiments():
    scenario_1 = [
        {
            "subject": "Bases de Datos Avanzadas",
            "topic": "Consultas OQL y objetos",
            "grade": 68,
            "progress": 55,
            "days_to_exam": 4,
            "impact": 0.80
        },
        {
            "subject": "Graficacion y Videojuegos",
            "topic": "Shaders y transformaciones",
            "grade": 72,
            "progress": 60,
            "days_to_exam": 3,
            "impact": 0.85
        },
        {
            "subject": "Redes y Telecomunicaciones",
            "topic": "Modelo OSI y direccionamiento IP",
            "grade": 64,
            "progress": 45,
            "days_to_exam": 2,
            "impact": 0.90
        },
        {
            "subject": "Sistemas Operativos",
            "topic": "Procesos e hilos",
            "grade": 70,
            "progress": 50,
            "days_to_exam": 5,
            "impact": 0.75
        },
        {
            "subject": "Inteligencia Artificial",
            "topic": "Agentes inteligentes y heuristicas",
            "grade": 82,
            "progress": 70,
            "days_to_exam": 6,
            "impact": 0.70
        }
    ]

    scenario_2 = [
        {
            "subject": "Bases de Datos Avanzadas",
            "topic": "Consultas OQL y objetos",
            "grade": 68,
            "progress": 55,
            "days_to_exam": 4,
            "impact": 0.80
        },
        {
            "subject": "Graficacion y Videojuegos",
            "topic": "Shaders y transformaciones",
            "grade": 72,
            "progress": 60,
            "days_to_exam": 1,
            "impact": 0.85
        },
        {
            "subject": "Redes y Telecomunicaciones",
            "topic": "Modelo OSI y direccionamiento IP",
            "grade": 64,
            "progress": 45,
            "days_to_exam": 5,
            "impact": 0.90
        },
        {
            "subject": "Sistemas Operativos",
            "topic": "Procesos e hilos",
            "grade": 70,
            "progress": 50,
            "days_to_exam": 5,
            "impact": 0.75
        },
        {
            "subject": "Inteligencia Artificial",
            "topic": "Agentes inteligentes y heuristicas",
            "grade": 82,
            "progress": 70,
            "days_to_exam": 6,
            "impact": 0.70
        }
    ]

    scenario_3 = [
        {
            "subject": "Bases de Datos Avanzadas",
            "topic": "Consultas OQL y objetos",
            "grade": 80,
            "progress": 75,
            "days_to_exam": 6,
            "impact": 0.80
        },
        {
            "subject": "Graficacion y Videojuegos",
            "topic": "Shaders y transformaciones",
            "grade": 78,
            "progress": 70,
            "days_to_exam": 5,
            "impact": 0.85
        },
        {
            "subject": "Redes y Telecomunicaciones",
            "topic": "Modelo OSI y direccionamiento IP",
            "grade": 84,
            "progress": 82,
            "days_to_exam": 7,
            "impact": 0.75
        },
        {
            "subject": "Sistemas Operativos",
            "topic": "Procesos e hilos",
            "grade": 58,
            "progress": 35,
            "days_to_exam": 4,
            "impact": 0.90
        },
        {
            "subject": "Inteligencia Artificial",
            "topic": "Agentes inteligentes y heuristicas",
            "grade": 88,
            "progress": 85,
            "days_to_exam": 8,
            "impact": 0.70
        }
    ]

    print("Resultados de experimentos - StudyHelperAI")
    print("=" * 90)
    print("Formula utilizada:")
    print("f(s,a) = 0.40(dificultad) + 0.35(impacto) + 0.25(urgencia)")
    print()

    run_scenario(
        "Caso base: Redes tiene bajo rendimiento y examen cercano",
        scenario_1,
        total_available_hours=5
    )

    run_scenario(
        "Cambio en el entorno: Graficacion tiene examen mañana",
        scenario_2,
        total_available_hours=5
    )

    run_scenario(
        "Cambio en el entorno: Sistemas Operativos tiene bajo progreso",
        scenario_3,
        total_available_hours=5
    )


def main():
    os.makedirs(os.path.dirname(RESULTS_PATH), exist_ok=True)

    with open(RESULTS_PATH, "w", encoding="utf-8") as file:
        with redirect_stdout(file):
            run_all_experiments()

    run_all_experiments()

    print(f"\nLos resultados tambien fueron guardados en: {RESULTS_PATH}")


if __name__ == "__main__":
    main()