import json
import os
import sys

sys.path.append(os.path.abspath(os.path.dirname(__file__)))

from agent import StudyHelperAgent


def main():
    project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
    data_path = os.path.join(project_root, "data", "student_data.csv")

    profile = "balanced"
    total_available_hours = 5

    if len(sys.argv) > 1:
        profile = sys.argv[1]

    if len(sys.argv) > 2:
        try:
            total_available_hours = int(sys.argv[2])
        except ValueError:
            total_available_hours = 5

    agent = StudyHelperAgent(
        data_path=data_path,
        total_available_hours=total_available_hours,
        profile=profile
    )

    state = agent.load_state()
    result = agent.decide(state)

    output = {
        "project": "StudyHelperAI",
        "profile": result["profile"],
        "weights": result["weights"],
        "initial_state": state,
        "ranked_actions": result["ranked_actions"],
        "study_plan": result["study_plan"],
        "updated_state": result["updated_state"],
        "metrics": result["metrics"]
    }

    print(json.dumps(output, ensure_ascii=False))


if __name__ == "__main__":
    main()