import csv
from copy import deepcopy


class StudyHelperAgent:
    def __init__(self, data_path=None, total_available_hours=5):
        self.data_path = data_path
        self.total_available_hours = total_available_hours

        # Pesos de la funcion de decision
        self.weight_difficulty = 0.40
        self.weight_impact = 0.35
        self.weight_urgency = 0.25

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
                    "impact": float(row["impact"])
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

    def calculate_urgency(self, days_to_exam):
        """
        Calcula la urgencia.
        Entre menos dias falten para el examen, mayor sera la urgencia.
        """
        if days_to_exam <= 0:
            return 1.0

        urgency = 1 / days_to_exam
        return urgency

    def score_action(self, item):
        """
        Funcion de decision:
        f(s,a) = 0.40(dificultad) + 0.35(impacto) + 0.25(urgencia)
        """
        difficulty = self.calculate_difficulty(item["grade"], item["progress"])
        urgency = self.calculate_urgency(item["days_to_exam"])
        impact = item["impact"]

        score = (
            self.weight_difficulty * difficulty +
            self.weight_impact * impact +
            self.weight_urgency * urgency
        )

        return {
            "subject": item["subject"],
            "topic": item["topic"],
            "grade": item["grade"],
            "progress": item["progress"],
            "days_to_exam": item["days_to_exam"],
            "impact": impact,
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

    def recommend_hours(self, action):
        """
        Recomienda horas de estudio segun dificultad, urgencia y puntaje.
        """
        hours = 1

        if action["score"] >= 0.60:
            hours = 3
        elif action["score"] >= 0.50:
            hours = 2
        else:
            hours = 1

        if action["days_to_exam"] <= 2:
            hours += 1

        return hours

    def generate_study_plan(self, state):
        """
        Genera un plan de estudio usando el ranking de prioridades
        y el total de horas disponibles.
        """
        ranked_actions = self.rank_actions(state)

        remaining_hours = self.total_available_hours
        study_plan = []

        for action in ranked_actions:
            if remaining_hours <= 0:
                break

            recommended_hours = self.recommend_hours(action)

            if recommended_hours > remaining_hours:
                recommended_hours = remaining_hours

            if recommended_hours > 0:
                study_plan.append({
                    "subject": action["subject"],
                    "topic": action["topic"],
                    "assigned_hours": recommended_hours,
                    "score": action["score"],
                    "difficulty": action["difficulty"],
                    "impact": action["impact"],
                    "urgency": action["urgency"]
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

        metrics = {
            "average_score": average_score,
            "best_score": best_score,
            "score_difference": score_difference,
            "assigned_hours": assigned_hours,
            "available_hours": self.total_available_hours,
            "time_usage": time_usage
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
            "ranked_actions": ranked_actions,
            "study_plan": study_plan,
            "updated_state": updated_state,
            "metrics": metrics
        }

    def choose_best_action(self, state):
        """
        Mantiene compatibilidad con la version anterior.
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