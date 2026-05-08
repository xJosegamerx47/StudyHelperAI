<?php
$projectRoot = dirname(__DIR__);
$csvPath = $projectRoot . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "student_data.csv";
$pythonScript = $projectRoot . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "web_output.py";

$fields = [
    "subject",
    "topic",
    "grade",
    "progress",
    "days_to_exam",
    "impact",
    "task_load",
    "subjective_difficulty",
    "available_days",
    "learning_style"
];

$fieldLabels = [
    "subject" => "Materia",
    "topic" => "Tema",
    "grade" => "Calificación",
    "progress" => "Progreso",
    "days_to_exam" => "Días examen",
    "impact" => "Impacto",
    "task_load" => "Carga tareas",
    "subjective_difficulty" => "Dificultad subjetiva",
    "available_days" => "Días disponibles",
    "learning_style" => "Estilo"
];

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
    return $hours === 1 ? "1 hora" : $hours . " horas";
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

function readCsvData($csvPath) {
    $rows = [];

    if (!file_exists($csvPath)) {
        return $rows;
    }

    $file = fopen($csvPath, "r");
    $headers = fgetcsv($file);

    while (($data = fgetcsv($file)) !== false) {
        $row = [];
        foreach ($headers as $index => $header) {
            $row[$header] = $data[$index] ?? "";
        }
        $rows[] = $row;
    }

    fclose($file);
    return $rows;
}

function writeCsvData($csvPath, $rows, $fields) {
    $file = fopen($csvPath, "w");

    fputcsv($file, $fields);

    foreach ($rows as $row) {
        $line = [];
        foreach ($fields as $field) {
            $line[] = $row[$field] ?? "";
        }
        fputcsv($file, $line);
    }

    fclose($file);
}

$profile = $_GET["profile"] ?? "balanced";
$hours = $_GET["hours"] ?? "5";
$message = "";

$allowedProfiles = ["balanced", "urgent", "low_performance"];

if (!in_array($profile, $allowedProfiles)) {
    $profile = "balanced";
}

if (!is_numeric($hours) || intval($hours) <= 0) {
    $hours = "5";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_data"])) {
    $newRows = [];

    if (isset($_POST["rows"]) && is_array($_POST["rows"])) {
        foreach ($_POST["rows"] as $row) {
            if (trim($row["subject"] ?? "") === "" || trim($row["topic"] ?? "") === "") {
                continue;
            }

            $newRows[] = [
                "subject" => $row["subject"] ?? "",
                "topic" => $row["topic"] ?? "",
                "grade" => $row["grade"] ?? "0",
                "progress" => $row["progress"] ?? "0",
                "days_to_exam" => $row["days_to_exam"] ?? "1",
                "impact" => $row["impact"] ?? "0",
                "task_load" => $row["task_load"] ?? "1",
                "subjective_difficulty" => $row["subjective_difficulty"] ?? "0",
                "available_days" => $row["available_days"] ?? "1",
                "learning_style" => $row["learning_style"] ?? "practice"
            ];
        }
    }

    writeCsvData($csvPath, $newRows, $fields);
    $message = "Datos guardados correctamente en data/student_data.csv";
}

$csvRows = readCsvData($csvPath);

$command = "python " . escapeshellarg($pythonScript) . " " . escapeshellarg($profile) . " " . escapeshellarg($hours);
$jsonOutput = shell_exec($command);
$data = json_decode($jsonOutput, true);
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
            max-width: 1300px;
            margin: auto;
        }

        h1 {
            margin-bottom: 5px;
            letter-spacing: 2px;
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
            padding: 9px;
            text-align: left;
        }

        th {
            background: #eef2f7;
        }

        input, select, button {
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #bbb;
            font-size: 14px;
        }

        input[type="number"] {
            width: 80px;
        }

        .wide-input {
            width: 190px;
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

        .controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
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

        .success {
            background: #e2f3e8;
            color: #146c2e;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .error {
            background: #ffe5e5;
            color: #9b0000;
            padding: 15px;
            border-radius: 8px;
        }

        .small-note {
            color: #666;
            font-size: 14px;
        }

        .table-wrapper {
            overflow-x: auto;
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

    <div class="card">
        <h2>Editar datos académicos</h2>
        <p class="small-note">
            Modifica los valores del estado del estudiante y guarda los cambios en el archivo CSV.
        </p>

        <?php if ($message !== ""): ?>
            <div class="success"><?php echo safe($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="table-wrapper">
                <table>
                    <tr>
                        <?php foreach ($fields as $field): ?>
                            <th><?php echo safe($fieldLabels[$field]); ?></th>
                        <?php endforeach; ?>
                    </tr>

                    <?php foreach ($csvRows as $index => $row): ?>
                        <tr>
                            <td><input class="wide-input" name="rows[<?php echo $index; ?>][subject]" value="<?php echo safe($row["subject"]); ?>"></td>
                            <td><input class="wide-input" name="rows[<?php echo $index; ?>][topic]" value="<?php echo safe($row["topic"]); ?>"></td>
                            <td><input type="number" name="rows[<?php echo $index; ?>][grade]" value="<?php echo safe($row["grade"]); ?>" min="0" max="100"></td>
                            <td><input type="number" name="rows[<?php echo $index; ?>][progress]" value="<?php echo safe($row["progress"]); ?>" min="0" max="100"></td>
                            <td><input type="number" name="rows[<?php echo $index; ?>][days_to_exam]" value="<?php echo safe($row["days_to_exam"]); ?>" min="1"></td>
                            <td><input type="number" step="0.01" name="rows[<?php echo $index; ?>][impact]" value="<?php echo safe($row["impact"]); ?>" min="0" max="1"></td>
                            <td><input type="number" name="rows[<?php echo $index; ?>][task_load]" value="<?php echo safe($row["task_load"]); ?>" min="1" max="5"></td>
                            <td><input type="number" step="0.01" name="rows[<?php echo $index; ?>][subjective_difficulty]" value="<?php echo safe($row["subjective_difficulty"]); ?>" min="0" max="1"></td>
                            <td><input type="number" name="rows[<?php echo $index; ?>][available_days]" value="<?php echo safe($row["available_days"]); ?>" min="1"></td>
                            <td>
                                <select name="rows[<?php echo $index; ?>][learning_style]">
                                    <option value="practice" <?php if ($row["learning_style"] === "practice") echo "selected"; ?>>Práctica</option>
                                    <option value="visual" <?php if ($row["learning_style"] === "visual") echo "selected"; ?>>Visual</option>
                                    <option value="reading" <?php if ($row["learning_style"] === "reading") echo "selected"; ?>>Lectura</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <br>
            <button type="submit" name="save_data" value="1">Guardar datos</button>
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