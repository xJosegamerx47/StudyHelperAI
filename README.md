# StudyHelperAI

StudyHelperAI es un sistema de recomendación de estudio basado en un agente inteligente. Analiza el estado académico de un estudiante y genera un plan personalizado considerando calificación, progreso, urgencia, impacto académico, carga de tareas, dificultad subjetiva y estilo de aprendizaje.

El agente no elige materias al azar: evalúa cada posible acción de estudio con una función heurística ponderada, genera un ranking de prioridades, asigna horas de estudio, recomienda actividades y simula cómo cambiaría el progreso después de aplicar el plan.

---

## Autores

- José Luis Godínez Carrillo
- Aldo Solís Rincón
- Nathan Michel Beiza Varela

---

## Objetivo del proyecto

Implementar un agente inteligente funcional que:

- Reciba un estado académico `s` desde un archivo CSV.
- Evalúe acciones posibles `a`, donde cada acción representa estudiar una materia/tema.
- Calcule una puntuación mediante una función de decisión `f(s, a)`.
- Genere un ranking de prioridades.
- Asigne horas de estudio según prioridad, urgencia y dificultad.
- Recomiende actividades y recursos de acuerdo con el estilo de aprendizaje.
- Simule la actualización del progreso después de aplicar el plan.
- Permita observar resultados por consola, experimentos controlados e interfaz web.

---

## Definición formal del agente

### Estado `s`

El estado del estudiante se encuentra en `data/student_data.csv`. Cada fila contiene:

- `subject`: materia.
- `topic`: tema específico.
- `grade`: calificación actual.
- `progress`: avance actual en el tema.
- `days_to_exam`: días restantes antes del examen.
- `impact`: impacto académico de la materia, de 0 a 1.
- `task_load`: carga de tareas, de 1 a 5.
- `subjective_difficulty`: dificultad percibida, de 0 a 1.
- `available_days`: días reales disponibles para estudiar.
- `learning_style`: estilo de aprendizaje (`practice`, `visual`, `reading`).

### Acciones `a`

Cada acción posible consiste en estudiar una combinación de materia y tema durante cierto número de horas.

### Función de decisión `f(s, a)`

```text
f(s,a) = w1(dificultad) + w2(impacto) + w3(urgencia) + w4(carga_tareas) + w5(dificultad_subjetiva)
```

La acción principal se interpreta como:

```text
a* = arg max f(s, a)
```

El sistema usa tres perfiles de decisión:

| Perfil | Enfoque |
|---|---|
| `balanced` | Balancea dificultad, impacto, urgencia y carga. |
| `urgent` | Prioriza exámenes cercanos y menor disponibilidad. |
| `low_performance` | Prioriza bajo rendimiento y bajo progreso. |

---

## Estructura del proyecto

```text
StudyHelperAI-main/
│
├── data/
│   └── student_data.csv
│
├── experiments/
│   └── experiment_1.py
│
├── results/
│   ├── results.txt
│   └── experiment_results.txt
│
├── src/
│   ├── agent.py
│   ├── main.py
│   └── web_output.py
│
├── web/
│   └── index.php
│
├── requirements.txt
├── run_web_windows.bat
└── README.md
```

---

## Dependencias

### Python

Se requiere Python 3. El proyecto usa únicamente librerías estándar de Python, por lo que no necesita instalar paquetes externos.

Verificar Python:

```bash
python --version
```

### PHP

Para la interfaz web se requiere PHP 8 o superior.

Verificar PHP:

```bash
php -v
```

---

## Cómo ejecutar el sistema

### 1. Ejecución por consola

Desde la carpeta del proyecto:

```bash
python src/main.py
```

Salida esperada:

```text
StudyHelperAI - Agente inteligente de recomendacion de estudio
Ranking de prioridades calculado por el agente
Plan de estudio generado
Estado actualizado despues de aplicar el plan
Metricas iniciales del agente
```

---

### 2. Ejecución de experimentos

```bash
python experiments/experiment_1.py
```

Esto ejecuta escenarios controlados para demostrar que el agente responde a cambios en el entorno, por ejemplo:

- Caso base con perfil balanceado.
- Cambio de urgencia por examen cercano.
- Cambio por bajo rendimiento en una materia.

Los resultados también se guardan en:

```text
results/experiment_results.txt
```

---

### 3. Interfaz web

Desde la carpeta del proyecto:

```bash
php -S localhost:8000 -t web
```

Luego abrir en el navegador:

```text
http://localhost:8000
```

En Windows también se puede abrir directamente:

```text
run_web_windows.bat
```

La interfaz web permite:

- Cambiar el perfil de decisión.
- Cambiar las horas disponibles.
- Editar el estado académico.
- Agregar nuevas materias/temas.
- Ver ranking de prioridades.
- Ver plan de estudio recomendado.
- Ver pesos activos y métricas.
- Ver el estado actualizado simulado.

---

## Ejemplo de ejecución

Con el estado inicial del archivo CSV, el agente puede recomendar estudiar primero:

```text
1. Redes y Telecomunicaciones - Modelo OSI y direccionamiento IP
   Prioridad: Alta
   Tiempo asignado: 3 horas
   Actividad recomendada: Resolver ejercicios practicos y revisar errores

2. Bases de Datos Avanzadas - Consultas OQL y objetos
   Prioridad: Media
   Tiempo asignado: 2 horas
```

Esto ocurre porque el agente detecta mayor urgencia, dificultad, impacto y carga académica en las primeras acciones.

---

## Evidencia de comportamiento inteligente

El proyecto demuestra comportamiento inteligente porque:

- Recibe un estado inicial estructurado.
- Evalúa múltiples acciones posibles.
- Aplica una función heurística con pesos configurables.
- Selecciona acciones con base en puntuaciones calculadas.
- Cambia sus decisiones cuando cambian las variables del entorno.
- Produce resultados medibles mediante métricas y experimentos.

---

## Limitaciones

- El sistema usa una heurística definida manualmente, no aprendizaje automático entrenado.
- La mejora del progreso es simulada.
- No se conecta a plataformas reales como Moodle, Canvas o Google Calendar.
- Los estilos de aprendizaje se simplifican en tres categorías.

---

## Notas para demostración en video

Una demostración breve puede seguir esta estructura:

1. Explicar el problema: elegir qué estudiar primero.
2. Mostrar el CSV como estado del estudiante.
3. Ejecutar `python src/main.py`.
4. Mostrar el ranking y el plan generado.
5. Abrir la interfaz web.
6. Cambiar un valor, por ejemplo días para examen o calificación.
7. Recalcular y explicar cómo cambia la decisión del agente.
