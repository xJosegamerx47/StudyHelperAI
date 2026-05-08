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
    print("-" * 100)

    for item in state:
        print(
            f"- {item['subject']} | Tema: {item['topic']} | "
            f"Calificacion: {item['grade']} | Progreso: {item['progress']}% | "
            f"Dias examen: {item['days_to_exam']} | Impacto: {item['impact']} | "
            f"Carga tareas: {item['task_load']} | Dificultad subjetiva: {item['subjective_difficulty']} | "
            f"Dias disponibles: {item['available_days']} | Estilo: {item['learning_style']}"
        )


def print_profile(result):
    print("\nPerfil de decision:")
    print("-" * 100)
    print(f"Perfil usado: {result['profile']}")

    print("\nPesos activos:")
    for factor, weight in result["weights"].items():
        print(f"- {factor}: {weight}")


def print_ranking(ranked_actions):
    print("\nRanking de prioridades:")
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
    print("\nPlan de estudio:")
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
    print("\nMetricas del escenario:")
    print("-" * 100)

    print(f"Puntaje promedio evaluado: {metrics['average_score']:.3f}")
    print(f"Mejor puntaje encontrado: {metrics['best_score']:.3f}")
    print(f"Diferencia entre primera y segunda opcion: {metrics['score_difference']:.3f}")
    print(f"Horas asignadas: {metrics['assigned_hours']}")
    print(f"Horas disponibles: {metrics['available_hours']}")
    print(f"Uso del tiempo disponible: {metrics['time_usage'] * 100:.1f}%")
    print(f"Cantidad de temas de prioridad alta en el plan: {metrics['high_priority_count']}")


def run_scenario(name, state, profile="balanced", total_available_hours=5):
    agent = StudyHelperAgent(
        data_path=None,
        total_available_hours=total_available_hours,
        profile=profile
    )

    result = agent.decide(state)

    print("=" * 100)
    print(f"ESCENARIO: {name}")
    print("=" * 100)

    print_state(state, "Estado inicial")
    print_profile(result)

    print("\nFormula utilizada:")
    print(
        "f(s,a) = w1(dificultad) + w2(impacto) + w3(urgencia) "
        "+ w4(carga_tareas) + w5(dificultad_subjetiva)"
    )

    print_ranking(result["ranked_actions"])
    print_study_plan(result["study_plan"])
    print_state(result["updated_state"], "Estado actualizado despues del plan")
    print_metrics(result["metrics"])

    print("\nInterpretacion:")
    print(
        "El agente evaluo todas las acciones posibles, uso el perfil de pesos seleccionado, "
        "ordeno las materias por prioridad y genero un plan de estudio considerando variables academicas "
        "y personales del estudiante."
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
            "impact": 0.80,
            "task_load": 3,
            "subjective_difficulty": 0.70,
            "available_days": 3,
            "learning_style": "practice"
        },
        {
            "subject": "Graficacion y Videojuegos",
            "topic": "Shaders y transformaciones",
            "grade": 72,
            "progress": 60,
            "days_to_exam": 3,
            "impact": 0.85,
            "task_load": 2,
            "subjective_difficulty": 0.65,
            "available_days": 2,
            "learning_style": "visual"
        },
        {
            "subject": "Redes y Telecomunicaciones",
            "topic": "Modelo OSI y direccionamiento IP",
            "grade": 64,
            "progress": 45,
            "days_to_exam": 2,
            "impact": 0.90,
            "task_load": 4,
            "subjective_difficulty": 0.85,
            "available_days": 2,
            "learning_style": "practice"
        },
        {
            "subject": "Sistemas Operativos",
            "topic": "Procesos e hilos",
            "grade": 70,
            "progress": 50,
            "days_to_exam": 5,
            "impact": 0.75,
            "task_load": 3,
            "subjective_difficulty": 0.75,
            "available_days": 4,
            "learning_style": "reading"
        },
        {
            "subject": "Inteligencia Artificial",
            "topic": "Agentes inteligentes y heuristicas",
            "grade": 82,
            "progress": 70,
            "days_to_exam": 6,
            "impact": 0.70,
            "task_load": 2,
            "subjective_difficulty": 0.60,
            "available_days": 5,
            "learning_style": "practice"
        }
    ]

    scenario_2 = [
        {
            "subject": "Bases de Datos Avanzadas",
            "topic": "Consultas OQL y objetos",
            "grade": 68,
            "progress": 55,
            "days_to_exam": 4,
            "impact": 0.80,
            "task_load": 3,
            "subjective_difficulty": 0.70,
            "available_days": 3,
            "learning_style": "practice"
        },
        {
            "subject": "Graficacion y Videojuegos",
            "topic": "Shaders y transformaciones",
            "grade": 72,
            "progress": 60,
            "days_to_exam": 1,
            "impact": 0.85,
            "task_load": 4,
            "subjective_difficulty": 0.80,
            "available_days": 1,
            "learning_style": "visual"
        },
        {
            "subject": "Redes y Telecomunicaciones",
            "topic": "Modelo OSI y direccionamiento IP",
            "grade": 64,
            "progress": 45,
            "days_to_exam": 5,
            "impact": 0.90,
            "task_load": 4,
            "subjective_difficulty": 0.85,
            "available_days": 4,
            "learning_style": "practice"
        },
        {
            "subject": "Sistemas Operativos",
            "topic": "Procesos e hilos",
            "grade": 70,
            "progress": 50,
            "days_to_exam": 5,
            "impact": 0.75,
            "task_load": 3,
            "subjective_difficulty": 0.75,
            "available_days": 4,
            "learning_style": "reading"
        },
        {
            "subject": "Inteligencia Artificial",
            "topic": "Agentes inteligentes y heuristicas",
            "grade": 82,
            "progress": 70,
            "days_to_exam": 6,
            "impact": 0.70,
            "task_load": 2,
            "subjective_difficulty": 0.60,
            "available_days": 5,
            "learning_style": "practice"
        }
    ]

    scenario_3 = [
        {
            "subject": "Bases de Datos Avanzadas",
            "topic": "Consultas OQL y objetos",
            "grade": 80,
            "progress": 75,
            "days_to_exam": 6,
            "impact": 0.80,
            "task_load": 3,
            "subjective_difficulty": 0.60,
            "available_days": 5,
            "learning_style": "practice"
        },
        {
            "subject": "Graficacion y Videojuegos",
            "topic": "Shaders y transformaciones",
            "grade": 78,
            "progress": 70,
            "days_to_exam": 5,
            "impact": 0.85,
            "task_load": 2,
            "subjective_difficulty": 0.65,
            "available_days": 4,
            "learning_style": "visual"
        },
        {
            "subject": "Redes y Telecomunicaciones",
            "topic": "Modelo OSI y direccionamiento IP",
            "grade": 84,
            "progress": 82,
            "days_to_exam": 7,
            "impact": 0.75,
            "task_load": 2,
            "subjective_difficulty": 0.55,
            "available_days": 5,
            "learning_style": "practice"
        },
        {
            "subject": "Sistemas Operativos",
            "topic": "Procesos e hilos",
            "grade": 58,
            "progress": 35,
            "days_to_exam": 4,
            "impact": 0.90,
            "task_load": 5,
            "subjective_difficulty": 0.90,
            "available_days": 3,
            "learning_style": "reading"
        },
        {
            "subject": "Inteligencia Artificial",
            "topic": "Agentes inteligentes y heuristicas",
            "grade": 88,
            "progress": 85,
            "days_to_exam": 8,
            "impact": 0.70,
            "task_load": 2,
            "subjective_difficulty": 0.50,
            "available_days": 6,
            "learning_style": "practice"
        }
    ]

    print("Resultados de experimentos - StudyHelperAI")
    print("=" * 100)

    run_scenario(
        "Caso base con perfil balanceado",
        scenario_1,
        profile="balanced",
        total_available_hours=5
    )

    run_scenario(
        "Cambio en el entorno: examen urgente en Graficacion",
        scenario_2,
        profile="urgent",
        total_available_hours=5
    )

    run_scenario(
        "Cambio en el entorno: bajo rendimiento en Sistemas Operativos",
        scenario_3,
        profile="low_performance",
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