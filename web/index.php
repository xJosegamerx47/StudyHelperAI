<?php
$profile = $_GET["profile"] ?? "balanced";
$hours = $_GET["hours"] ?? "5";

$allowedProfiles = ["balanced", "urgent", "low_performance"];

if (!in_array($profile, $allowedProfiles)) {
    $profile = "balanced";
}

if (!is_numeric($hours) || intval($hours) <= 0) {
    $hours = "5";
}

$projectRoot = dirname(__DIR__);
$pythonScript = $projectRoot . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "web_output.py";

$command = "python " . escapeshellarg($pythonScript) . " " . escapeshellarg($profile) . " " . escapeshellarg($hours);
$jsonOutput = shell_exec($command);

$data = json_decode($jsonOutput, true);

function safe($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function formatScore($value) {
    return number_format((float)$value, 3);
}

function formatPercent($value) {
    return number_format((float)$value, 2) . "%";
}

function formatHours($hours) {
    $hours = (int)$hours;
    if ($hours === 1) {
        return "1 hora";
    }
    return $hours . " horas";
}

function profileLabel($profile) {
    $labels = [
        "balanced" => "Balanced",
        "urgent" => "Urgent",
        "low_performance" => "Low performance"
    ];

    return $labels[$profile] ?? $profile;
}

function factorLabel($factor) {
    $labels = [
        "difficulty" => "Dificultad",
        "impact" => "Impacto",
        "urgency" => "Urgencia",
        "task_load" => "Carga de tareas",
        "subjective_difficulty" => "Dificultad subjetiva"
    ];

    return $labels[$factor] ?? $factor;
}

function styleLabel($style) {
    $labels = [
        "practice" => "Práctica",
        "visual" => "Visual",
        "reading" => "Lectura"
    ];

    return $labels[$style] ?? $style;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>StudyHelperAI</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
            color: #222;
        }

        .container {
            max-width: 1250px;
            margin: auto;
        }

        h1 {
            margin-bottom: 5px;
            letter-spacing: 2px;
        }

        h2 {
            margin-top: 0;
        }

        .subtitle {
            color: #555;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }

        th, td {
            border-bottom: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #eef2f7;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            background: #e8f0fe;
            font-weight: bold;
        }

        .badge-high {
            background: #ffe2e2;
            color: #9b0000;
        }

        .badge-medium {
            background: #fff3cd;
            color: #7a5b00;
        }

        .badge-low {
            background: #e2f3e8;
            color: #146c2e;
        }

        .plan-item {
            border-left: 5px solid #2f6fed;
            padding: 18px;
            margin-bottom: 15px;
            background: #f8fbff;
            border-radius: 8px;
        }

        .plan-item h3 {
            margin-top: 0;
        }

        .controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        select, input, button {
            padding: 9px 11px;
            border-radius: 6px;
            border: 1px solid #bbb;
            font-size: 14px;
        }

        button {
            background: #2f6fed;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #1f55c9;
        }

        .error {
            background: #ffe5e5;
            color: #9b0000;
            padding: 15px;
            border-radius: 8px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        .small-note {
            color: #666;
            font-size: 14px;
            margin-top: 8px;
        }
    </style>
</head>
<body>
<div class="container">

    <h1>StudyHelperAI</h1>
    <p class="subtitle">Agente inteligente de recomendación de estudio</p>

    <div class="card">
        <form method="GET" class="controls">
            <label>
                Perfil:
                <select name="profile">
                    <option value="balanced" <?php if ($profile === "balanced") echo "selected"; ?>>Balanced</option>
                    <option value="urgent" <?php if ($profile === "urgent") echo "selected"; ?>>Urgent</option>
                    <option value="low_performance" <?php if ($profile === "low_performance") echo "selected"; ?>>Low performance</option>
                </select>
            </label>

            <label>
                Horas disponibles:
                <input type="number" name="hours" value="<?php echo safe($hours); ?>" min="1" max="12">
            </label>

            <button type="submit">Ejecutar agente</button>
        </form>
    </div>

    <?php if (!$data): ?>
        <div class="error">
            No se pudo ejecutar el agente. Revisa que Python funcione y que el archivo src/web_output.py exista.
        </div>
    <?php else: ?>

        <div class="card">
            <h2>Perfil de decisión</h2>
            <p>Perfil usado: <span class="badge"><?php echo safe(profileLabel($data["profile"])); ?></span></p>

            <table>
                <tr>
                    <th>Factor</th>
                    <th>Peso</th>
                </tr>
                <?php foreach ($data["weights"] as $factor => $weight): ?>
                    <tr>
                        <td><?php echo safe(factorLabel($factor)); ?></td>
                        <td><?php echo safe($weight); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <p class="small-note">
                El perfil cambia los pesos usados por el agente para priorizar dificultad, impacto, urgencia, carga de tareas y dificultad subjetiva.
            </p>
        </div>

        <div class="card">
            <h2>Ranking de prioridades</h2>

            <table>
                <tr>
                    <th>#</th>
                    <th>Materia</th>
                    <th>Tema</th>
                    <th>Puntaje</th>
                    <th>Dificultad</th>
                    <th>Impacto</th>
                    <th>Urgencia</th>
                    <th>Carga</th>
                </tr>

                <?php foreach ($data["ranked_actions"] as $index => $action): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo safe($action["subject"]); ?></td>
                        <td><?php echo safe($action["topic"]); ?></td>
                        <td><?php echo formatScore($action["score"]); ?></td>
                        <td><?php echo formatScore($action["difficulty"]); ?></td>
                        <td><?php echo formatScore($action["impact"]); ?></td>
                        <td><?php echo formatScore($action["urgency"]); ?></td>
                        <td><?php echo formatScore($action["task_load"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="card">
            <h2>Plan de estudio generado</h2>

            <?php foreach ($data["study_plan"] as $item): ?>
                <?php
                    $priorityClass = "badge-low";
                    if ($item["priority"] === "Alta") {
                        $priorityClass = "badge-high";
                    } elseif ($item["priority"] === "Media") {
                        $priorityClass = "badge-medium";
                    }
                ?>

                <div class="plan-item">
                    <h3><?php echo safe($item["subject"]); ?> - <?php echo safe($item["topic"]); ?></h3>
                    <p><strong>Prioridad:</strong> <span class="badge <?php echo $priorityClass; ?>"><?php echo safe($item["priority"]); ?></span></p>
                    <p><strong>Tiempo asignado:</strong> <?php echo safe(formatHours($item["assigned_hours"])); ?></p>
                    <p><strong>Actividad recomendada:</strong> <?php echo safe($item["activity"]); ?></p>
                    <p><strong>Recurso recomendado:</strong> <?php echo safe($item["resource"]); ?></p>
                    <p><strong>Puntaje:</strong> <?php echo formatScore($item["score"]); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2>Estado actualizado después del plan</h2>
            <p class="small-note">
                Esta tabla simula cómo cambia el progreso del estudiante después de aplicar el plan de estudio.
            </p>

            <table>
                <tr>
                    <th>Materia</th>
                    <th>Tema</th>
                    <th>Calificación</th>
                    <th>Progreso actualizado</th>
                    <th>Días para examen</th>
                    <th>Estilo</th>
                </tr>

                <?php foreach ($data["updated_state"] as $item): ?>
                    <tr>
                        <td><?php echo safe($item["subject"]); ?></td>
                        <td><?php echo safe($item["topic"]); ?></td>
                        <td><?php echo safe($item["grade"]); ?></td>
                        <td><?php echo safe($item["progress"]); ?>%</td>
                        <td><?php echo safe($item["days_to_exam"]); ?></td>
                        <td><?php echo safe(styleLabel($item["learning_style"])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="card">
            <h2>Métricas</h2>

            <table>
                <tr>
                    <th>Métrica</th>
                    <th>Valor</th>
                </tr>
                <tr>
                    <td>Puntaje promedio evaluado</td>
                    <td><?php echo formatScore($data["metrics"]["average_score"]); ?></td>
                </tr>
                <tr>
                    <td>Mejor puntaje encontrado</td>
                    <td><?php echo formatScore($data["metrics"]["best_score"]); ?></td>
                </tr>
                <tr>
                    <td>Diferencia entre primera y segunda opción</td>
                    <td><?php echo formatScore($data["metrics"]["score_difference"]); ?></td>
                </tr>
                <tr>
                    <td>Horas asignadas</td>
                    <td><?php echo safe($data["metrics"]["assigned_hours"]); ?></td>
                </tr>
                <tr>
                    <td>Horas disponibles</td>
                    <td><?php echo safe($data["metrics"]["available_hours"]); ?></td>
                </tr>
                <tr>
                    <td>Uso del tiempo disponible</td>
                    <td><?php echo formatPercent($data["metrics"]["time_usage"] * 100); ?></td>
                </tr>
                <tr>
                    <td>Temas de prioridad alta</td>
                    <td><?php echo safe($data["metrics"]["high_priority_count"]); ?></td>
                </tr>
            </table>
        </div>

    <?php endif; ?>

</div>
</body>
</html>