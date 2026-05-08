import csv
from copy import deepcopy


class StudyHelperAgent:
    def __init__(self, data_path=None, total_available_hours=5, profile="balanced"):
        self.data_path = data_path
        self.total_available_hours = total_available_hours
        self.profile = profile

        self.weight_profiles = {
            "balanced": {
                "difficulty": 0.35,
                "impact": 0.25,
                "urgency": 0.20,
                "task_load": 0.10,
                "subjective_difficulty": 0.10
            },
            "urgent": {
                "difficulty": 0.25,
                "impact": 0.20,
                "urgency": 0.35,
                "task_load": 0.10,
                "subjective_difficulty": 0.10
            },
            "low_performance": {
                "difficulty": 0.45,
                "impact": 0.20,
                "urgency": 0.15,
                "task_load": 0.10,
                "subjective_difficulty": 0.10
            }
        }

        if self.profile not in self.weight_profiles:
            self.profile = "balanced"

        self.weights = self.weight_profiles[self.profile]

    def load_state(self):
        """
        Carga el estado del sistema desde un archivo CSV.
        Cada fila representa una posible accion de estudio.
        """
        state = []

        with open(self.data_path, mode="r", encoding="utf-8") as file:
            reader = csv.DictReader(file)

            for row in reader:
                state.append({
                    "subject": row["subject"],
                    "topic": row["topic"],
                    "grade": float(row["grade"]),
                    "progress": float(row["progress"]),
                    "days_to_exam": float(row["days_to_exam"]),
                    "impact": float(row["impact"]),
                    "task_load": float(row["task_load"]),
                    "subjective_difficulty": float(row["subjective_difficulty"]),
                    "available_days": float(row["available_days"]),
                    "learning_style": row["learning_style"]
                })

        return state

    def calculate_difficulty(self, grade, progress):
        """
        Calcula la dificultad con base en baja calificacion y bajo progreso.
        Entre menor sea la calificacion y el progreso, mayor sera la dificultad.
        """
        grade_factor = (100 - grade) / 100
        progress_factor = (100 - progress) / 100

        difficulty = (grade_factor + progress_factor) / 2
        return difficulty

    def calculate_urgency(self, days_to_exam, available_days):
        """
        Calcula la urgencia considerando dias para examen y dias reales disponibles.
        Entre menos dias falten y menos dias disponibles tenga el estudiante, mayor sera la urgencia.
        """
        if days_to_exam <= 0:
            exam_urgency = 1.0
        else:
            exam_urgency = 1 / days_to_exam

        if available_days <= 0:
            availability_urgency = 1.0
        else:
            availability_urgency = 1 / available_days

        urgency = (exam_urgency + availability_urgency) / 2
        return urgency

    def normalize_task_load(self, task_load):
        """
        Normaliza la carga de tareas de escala 1-5 a escala 0-1.
        """
        return min(1.0, max(0.0, task_load / 5))

    def score_action(self, item):
        """
        Funcion de decision extendida:
        f(s,a) =
        w1(dificultad) + w2(impacto) + w3(urgencia)
        + w4(carga_tareas) + w5(dificultad_subjetiva)
        """
        difficulty = self.calculate_difficulty(item["grade"], item["progress"])
        urgency = self.calculate_urgency(item["days_to_exam"], item["available_days"])
        impact = item["impact"]
        task_load = self.normalize_task_load(item["task_load"])
        subjective_difficulty = item["subjective_difficulty"]

        score = (
            self.weights["difficulty"] * difficulty +
            self.weights["impact"] * impact +
            self.weights["urgency"] * urgency +
            self.weights["task_load"] * task_load +
            self.weights["subjective_difficulty"] * subjective_difficulty
        )

        return {
            "subject": item["subject"],
            "topic": item["topic"],
            "grade": item["grade"],
            "progress": item["progress"],
            "days_to_exam": item["days_to_exam"],
            "impact": impact,
            "task_load": task_load,
            "subjective_difficulty": subjective_difficulty,
            "available_days": item["available_days"],
            "learning_style": item["learning_style"],
            "difficulty": difficulty,
            "urgency": urgency,
            "score": score
        }

    def rank_actions(self, state):
        """
        Evalua todas las acciones posibles y las ordena de mayor a menor prioridad.
        """
        scored_actions = []

        for item in state:
            scored_actions.append(self.score_action(item))

        ranked_actions = sorted(
            scored_actions,
            key=lambda action: action["score"],
            reverse=True
        )

        return ranked_actions

    def get_priority_level(self, score):
        if score >= 0.60:
            return "Alta"
        if score >= 0.45:
            return "Media"
        return "Baja"

    def recommend_activity(self, learning_style):
        """
        Recomienda tipo de actividad segun el estilo de aprendizaje.
        """
        if learning_style == "visual":
            return "Ver material visual y hacer un mapa conceptual"

        if learning_style == "reading":
            return "Leer apuntes, resumir conceptos clave y responder preguntas"

        if learning_style == "practice":
            return "Resolver ejercicios practicos y revisar errores"

        return "Repasar el tema y resolver ejercicios de refuerzo"

    def recommend_resource(self, learning_style):
        """
        Recomienda recurso de estudio segun el estilo de aprendizaje.
        """
        if learning_style == "visual":
            return "Videos, diagramas y esquemas"

        if learning_style == "reading":
            return "Apuntes, libro de texto y resumen escrito"

        if learning_style == "practice":
            return "Ejercicios, problemas guiados y practicas"

        return "Material de repaso general"

    def recommend_hours(self, action):
        """
        Recomienda horas de estudio segun puntaje, urgencia, dificultad y carga de tareas.
        """
        hours = 1

        if action["score"] >= 0.60:
            hours = 3
        elif action["score"] >= 0.45:
            hours = 2
        else:
            hours = 1

        if action["urgency"] >= 0.50:
            hours += 1

        if action["difficulty"] >= 0.50:
            hours += 1

        if action["task_load"] >= 0.80:
            hours += 1

        return hours

    def generate_study_plan(self, state):
        """
        Genera un plan de estudio usando el ranking de prioridades
        y el total de horas disponibles.

        Para evitar que todo el tiempo se asigne a una sola materia,
        se limita el maximo de horas por tema.
        """
        ranked_actions = self.rank_actions(state)

        remaining_hours = self.total_available_hours
        study_plan = []
        max_hours_per_subject = 3

        for action in ranked_actions:
            if remaining_hours <= 0:
                break

            recommended_hours = self.recommend_hours(action)

            if recommended_hours > max_hours_per_subject:
                recommended_hours = max_hours_per_subject

            if recommended_hours > remaining_hours:
                recommended_hours = remaining_hours

            if recommended_hours > 0:
                study_plan.append({
                    "subject": action["subject"],
                    "topic": action["topic"],
                    "assigned_hours": recommended_hours,
                    "score": action["score"],
                    "priority": self.get_priority_level(action["score"]),
                    "difficulty": action["difficulty"],
                    "impact": action["impact"],
                    "urgency": action["urgency"],
                    "task_load": action["task_load"],
                    "subjective_difficulty": action["subjective_difficulty"],
                    "learning_style": action["learning_style"],
                    "activity": self.recommend_activity(action["learning_style"]),
                    "resource": self.recommend_resource(action["learning_style"])
                })

                remaining_hours -= recommended_hours

        return study_plan, ranked_actions

    def simulate_progress_update(self, state, study_plan):
        """
        Simula la actualizacion del progreso despues de aplicar el plan de estudio.
        Cada hora estudiada aumenta el progreso en 5 puntos porcentuales.
        """
        updated_state = deepcopy(state)

        for plan_item in study_plan:
            for item in updated_state:
                same_subject = item["subject"] == plan_item["subject"]
                same_topic = item["topic"] == plan_item["topic"]

                if same_subject and same_topic:
                    improvement = plan_item["assigned_hours"] * 5
                    item["progress"] = min(100, item["progress"] + improvement)

        return updated_state

    def calculate_metrics(self, ranked_actions, study_plan):
        """
        Calcula metricas simples para evaluar el comportamiento del agente.
        """
        if not ranked_actions:
            return {}

        total_score = sum(action["score"] for action in ranked_actions)
        average_score = total_score / len(ranked_actions)

        best_score = ranked_actions[0]["score"]

        if len(ranked_actions) > 1:
            second_best_score = ranked_actions[1]["score"]
            score_difference = best_score - second_best_score
        else:
            score_difference = 0

        assigned_hours = sum(item["assigned_hours"] for item in study_plan)
        time_usage = assigned_hours / self.total_available_hours

        high_priority_count = sum(
            1 for item in study_plan if item["priority"] == "Alta"
        )

        metrics = {
            "average_score": average_score,
            "best_score": best_score,
            "score_difference": score_difference,
            "assigned_hours": assigned_hours,
            "available_hours": self.total_available_hours,
            "time_usage": time_usage,
            "high_priority_count": high_priority_count
        }

        return metrics

    def decide(self, state):
        """
        Ejecuta el ciclo completo del agente:
        estado -> ranking -> plan -> estado actualizado -> metricas.
        """
        study_plan, ranked_actions = self.generate_study_plan(state)
        updated_state = self.simulate_progress_update(state, study_plan)
        metrics = self.calculate_metrics(ranked_actions, study_plan)

        return {
            "profile": self.profile,
            "weights": self.weights,
            "ranked_actions": ranked_actions,
            "study_plan": study_plan,
            "updated_state": updated_state,
            "metrics": metrics
        }

    def choose_best_action(self, state):
        """
        Devuelve la mejor accion individual.
        """
        ranked_actions = self.rank_actions(state)
        best_action = ranked_actions[0]
        recommended_hours = self.recommend_hours(best_action)

        return {
            "subject": best_action["subject"],
            "topic": best_action["topic"],
            "recommended_hours": recommended_hours,
            "score": best_action["score"],
            "difficulty": best_action["difficulty"],
            "impact": best_action["impact"],
            "urgency": best_action["urgency"],
            "message": (
                f"Estudiar {best_action['subject']} - {best_action['topic']} "
                f"durante {recommended_hours} horas."
            )
        }