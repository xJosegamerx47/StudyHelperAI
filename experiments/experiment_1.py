import sys
import os

sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "src")))

from agent import StudyHelperAgent


def run_scenario(name, state):
    agent = StudyHelperAgent(data_path=None)
    action = agent.choose_best_action(state)

    print("=" * 70)
    print(f"ESCENARIO: {name}")
    print("=" * 70)

    print("\nEstado de entrada:")
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
    print(f"Puntaje final f(s,a): {action['score']:.3f}")

    print("\nAccion:")
    print(action["message"])
    print()


def main():
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

    run_scenario("Caso base: se prioriza Redes por bajo rendimiento y examen cercano", scenario_1)
    run_scenario("Cambio en el entorno: Graficacion tiene examen mañana", scenario_2)
    run_scenario("Cambio en el entorno: Sistemas Operativos tiene bajo progreso", scenario_3)


if __name__ == "__main__":
    main()