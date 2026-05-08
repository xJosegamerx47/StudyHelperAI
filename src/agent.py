import csv


class StudyHelperAgent:
    def __init__(self, data_path):
        self.data_path = data_path

        # Pesos de la función de decisión
        self.weight_difficulty = 0.40
        self.weight_impact = 0.35
        self.weight_urgency = 0.25

    def load_state(self):
        """
        Carga el estado del sistema desde un archivo CSV.
        Cada fila representa un posible tema de estudio.
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
        Calcula la dificultad con base en baja calificación y bajo progreso.
        Entre menor sea la calificación y el progreso, mayor será la dificultad.
        """
        grade_factor = (100 - grade) / 100
        progress_factor = (100 - progress) / 100

        difficulty = (grade_factor + progress_factor) / 2
        return difficulty

    def calculate_urgency(self, days_to_exam):
        """
        Calcula la urgencia.
        Entre menos días falten para el examen, mayor será la urgencia.
        """
        urgency = 1 / days_to_exam
        return urgency

    def score_action(self, item):
        """
        Función de decisión:
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

        return score, difficulty, impact, urgency

    def choose_best_action(self, state):
        """
        Selecciona la acción con mayor puntuación.
        a* = arg max f(s,a)
        """
        best_item = None
        best_score = -1
        best_details = None

        for item in state:
            score, difficulty, impact, urgency = self.score_action(item)

            if score > best_score:
                best_score = score
                best_item = item
                best_details = {
                    "score": score,
                    "difficulty": difficulty,
                    "impact": impact,
                    "urgency": urgency
                }

        action = self.generate_action(best_item, best_details)
        return action

    def generate_action(self, item, details):
        """
        Genera la acción recomendada por el agente.
        """
        recommended_hours = 2

        if details["difficulty"] > 0.50:
            recommended_hours = 3

        if item["days_to_exam"] <= 2:
            recommended_hours += 1

        return {
            "subject": item["subject"],
            "topic": item["topic"],
            "recommended_hours": recommended_hours,
            "score": details["score"],
            "difficulty": details["difficulty"],
            "impact": details["impact"],
            "urgency": details["urgency"],
            "message": f"Estudiar {item['subject']} - {item['topic']} durante {recommended_hours} horas."
        }