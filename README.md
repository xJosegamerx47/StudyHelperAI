# StudyHelperAI

StudyHelperAI es un sistema de recomendación de estudio basado en un agente inteligente. El proyecto analiza el estado académico de un estudiante y genera un plan de estudio personalizado considerando desempeño, progreso, urgencia, carga de tareas, dificultad subjetiva y estilo de aprendizaje.

El agente toma decisiones mediante una función heurística ponderada, genera un ranking de prioridades, asigna horas de estudio, recomienda actividades y simula la actualización del progreso después de aplicar el plan.

---

## Autores

- José Luis Godínez Carrillo
- Aldo Solís Rincón
- Nathan Michel Beiza Varela

---

## Descripción general

El objetivo de StudyHelperAI es apoyar a los estudiantes en la toma de decisiones académicas cuando tienen varias materias, temas pendientes y evaluaciones próximas.

En lugar de recomendar una materia de forma aleatoria, el agente evalúa cada posible tema de estudio con base en diferentes variables del estado del estudiante. Después calcula una puntuación para cada opción y genera un plan de estudio con las materias más relevantes.

El sistema puede ejecutarse de tres formas:

1. Por consola, usando Python.
2. Mediante experimentos controlados.
3. Desde una interfaz web desarrollada en PHP.

---

## Problema que resuelve

Los estudiantes suelen tener dificultades para decidir qué materia estudiar primero cuando tienen varias tareas, exámenes y temas pendientes. Esta decisión puede depender de muchos factores, como:

- Calificación actual.
- Progreso en el tema.
- Días restantes antes del examen.
- Impacto académico de la materia.
- Carga de tareas.
- Dificultad percibida.
- Días reales disponibles para estudiar.
- Estilo de aprendizaje.

StudyHelperAI busca resolver este problema mediante un agente inteligente que evalúa esos factores y recomienda un plan de estudio justificado.

---

## Objetivo del proyecto

Implementar un agente inteligente funcional que:

- Reciba un estado académico del estudiante.
- Evalúe diferentes acciones posibles.
- Calcule un ranking de prioridades.
- Genere un plan de estudio personalizado.
- Recomiende actividades y recursos según el estilo de aprendizaje.
- Simule la actualización del progreso después del estudio.
- Permita observar resultados mediante consola e interfaz web.

---

## Arquitectura general del sistema

La arquitectura del proyecto se divide en cuatro capas principales:

```text
StudyHelperAI
│
├── Capa de datos
│   └── data/student_data.csv
│
├── Capa del agente inteligente
│   ├── src/agent.py
│   ├── src/main.py
│   └── src/web_output.py
│
├── Capa de experimentación
│   └── experiments/experiment_1.py
│
├── Capa de resultados
│   ├── results/results.txt
│   └── results/experiment_results.txt
│
└── Capa de interfaz web
    └── web/index.php