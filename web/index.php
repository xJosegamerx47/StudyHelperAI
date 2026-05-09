<?php
$projectRoot = dirname(__DIR__);
$csvPath = $projectRoot . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "student_data.csv";
$pythonScript = $projectRoot . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "web_output.py";

$fields = [
    "subject", "topic", "grade", "progress", "days_to_exam", "impact",
    "task_load", "subjective_difficulty", "available_days", "learning_style"
];

$fieldLabels = [
    "subject" => "Materia",
    "topic" => "Tema",
    "grade" => "Calificación",
    "progress" => "Progreso",
    "days_to_exam" => "Días examen",
    "impact" => "Impacto",
    "task_load" => "Tareas",
    "subjective_difficulty" => "Dificultad",
    "available_days" => "Días disponibles",
    "learning_style" => "Estilo"
];

$allowedProfiles = ["balanced", "urgent", "low_performance"];

function safe($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function clampNumber($value, $min, $max, $default) {
    if (!is_numeric($value)) {
        return $default;
    }
    $number = (float)$value;
    if ($number < $min) {
        return $min;
    }
    if ($number > $max) {
        return $max;
    }
    return $number;
}

function formatScore($value) {
    return number_format((float)$value, 3);
}

function formatPercent($value) {
    return number_format((float)$value, 1) . "%";
}

function formatHours($hours) {
    $hours = (int)$hours;
    return $hours === 1 ? "1 hora" : $hours . " horas";
}

function profileLabel($profile) {
    $labels = [
        "balanced" => "Balanceado",
        "urgent" => "Urgencia",
        "low_performance" => "Bajo rendimiento"
    ];
    return $labels[$profile] ?? $profile;
}

function profileDescription($profile) {
    $labels = [
        "balanced" => "Equilibra dificultad, impacto, urgencia y carga de tareas.",
        "urgent" => "Da más peso a exámenes cercanos y poco tiempo disponible.",
        "low_performance" => "Prioriza materias con menor calificación y menor avance."
    ];
    return $labels[$profile] ?? "Perfil personalizado.";
}

function factorLabel($factor) {
    $labels = [
        "difficulty" => "Dificultad objetiva",
        "impact" => "Impacto académico",
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
    if (!$file) {
        return $rows;
    }

    $headers = fgetcsv($file);
    if (!$headers) {
        fclose($file);
        return $rows;
    }

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
    if (!$file) {
        return false;
    }

    fputcsv($file, $fields);
    foreach ($rows as $row) {
        $line = [];
        foreach ($fields as $field) {
            $line[] = $row[$field] ?? "";
        }
        fputcsv($file, $line);
    }

    fclose($file);
    return true;
}

function runAgent($pythonScript, $profile, $hours) {
    $profileArg = escapeshellarg($profile);
    $hoursArg = escapeshellarg((string)$hours);
    $scriptArg = escapeshellarg($pythonScript);

    $commands = [
        "python " . $scriptArg . " " . $profileArg . " " . $hoursArg,
        "py " . $scriptArg . " " . $profileArg . " " . $hoursArg,
        "python3 " . $scriptArg . " " . $profileArg . " " . $hoursArg
    ];

    $lastOutput = "";
    foreach ($commands as $command) {
        $output = shell_exec($command . " 2>&1");
        $lastOutput = $output ?? "";
        $data = json_decode($lastOutput, true);
        if (is_array($data)) {
            return [$data, ""];
        }
    }

    return [null, $lastOutput];
}

$profile = $_GET["profile"] ?? "balanced";
if (!in_array($profile, $allowedProfiles)) {
    $profile = "balanced";
}

$hours = $_GET["hours"] ?? "5";
$hours = (int)clampNumber($hours, 1, 12, 5);
$message = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_data"])) {
    $newRows = [];

    if (isset($_POST["rows"]) && is_array($_POST["rows"])) {
        foreach ($_POST["rows"] as $row) {
            $subject = trim($row["subject"] ?? "");
            $topic = trim($row["topic"] ?? "");
            if ($subject === "" && $topic === "") {
                continue;
            }
            if ($subject === "" || $topic === "") {
                continue;
            }

            $style = $row["learning_style"] ?? "practice";
            if (!in_array($style, ["practice", "visual", "reading"])) {
                $style = "practice";
            }

            $newRows[] = [
                "subject" => $subject,
                "topic" => $topic,
                "grade" => clampNumber($row["grade"] ?? 0, 0, 100, 0),
                "progress" => clampNumber($row["progress"] ?? 0, 0, 100, 0),
                "days_to_exam" => clampNumber($row["days_to_exam"] ?? 1, 1, 365, 1),
                "impact" => clampNumber($row["impact"] ?? 0, 0, 1, 0),
                "task_load" => clampNumber($row["task_load"] ?? 1, 1, 5, 1),
                "subjective_difficulty" => clampNumber($row["subjective_difficulty"] ?? 0, 0, 1, 0),
                "available_days" => clampNumber($row["available_days"] ?? 1, 1, 365, 1),
                "learning_style" => $style
            ];
        }
    }

    if (writeCsvData($csvPath, $newRows, $fields)) {
        $message = "Datos guardados correctamente. El agente ya recalculó el plan con el nuevo estado.";
    } else {
        $errorMessage = "No se pudo guardar data/student_data.csv. Revisa permisos de escritura.";
    }
}

$csvRows = readCsvData($csvPath);
[$data, $agentError] = runAgent($pythonScript, $profile, $hours);

$bestAction = null;
if ($data && !empty($data["ranked_actions"])) {
    $bestAction = $data["ranked_actions"][0];
}

$plannedSubjects = $data ? count($data["study_plan"]) : 0;
$averageProgress = 0;
if ($data && !empty($data["initial_state"])) {
    $totalProgress = 0;
    foreach ($data["initial_state"] as $item) {
        $totalProgress += (float)$item["progress"];
    }
    $averageProgress = $totalProgress / count($data["initial_state"]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyHelperAI | Dashboard</title>
    <style>
        :root {
            --bg: #f5f7fb;
            --ink: #111827;
            --muted: #667085;
            --card: rgba(255, 255, 255, 0.82);
            --line: rgba(17, 24, 39, 0.10);
            --primary: #2563eb;
            --primary-soft: #dbeafe;
            --violet: #7c3aed;
            --green: #059669;
            --amber: #d97706;
            --red: #dc2626;
            --shadow: 0 24px 70px rgba(15, 23, 42, 0.12);
            --radius: 28px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.18), transparent 34rem),
                radial-gradient(circle at top right, rgba(124, 58, 237, 0.16), transparent 32rem),
                linear-gradient(180deg, #ffffff 0%, var(--bg) 44%, #eef2ff 100%);
        }

        a { color: inherit; }

        .page {
            width: min(1440px, calc(100% - 36px));
            margin: 0 auto;
            padding: 24px 0 48px;
        }

        .topbar {
            position: sticky;
            top: 14px;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            margin-bottom: 28px;
            border: 1px solid rgba(255,255,255,0.72);
            border-radius: 999px;
            background: rgba(255,255,255,0.72);
            backdrop-filter: blur(18px);
            box-shadow: 0 12px 38px rgba(15, 23, 42, 0.08);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .logo {
            width: 42px;
            height: 42px;
            border-radius: 15px;
            display: grid;
            place-items: center;
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--violet));
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.28);
        }

        .nav {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .nav a, .ghost-button {
            text-decoration: none;
            color: #344054;
            font-size: 14px;
            font-weight: 700;
            padding: 10px 14px;
            border-radius: 999px;
        }

        .nav a:hover, .ghost-button:hover { background: rgba(37,99,235,0.08); }

        .hero {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 24px;
            align-items: stretch;
            margin-bottom: 24px;
        }

        .hero-panel, .card, .metric, .plan-card, .rank-card {
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, 0.72);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
        }

        .hero-panel {
            min-height: 420px;
            border-radius: 38px;
            padding: 44px;
            position: relative;
            overflow: hidden;
        }

        .hero-panel::after {
            content: "";
            position: absolute;
            right: -160px;
            top: -160px;
            width: 420px;
            height: 420px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(37,99,235,0.20), transparent 65%);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.08);
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        h1 {
            margin: 0;
            font-size: clamp(44px, 7vw, 86px);
            line-height: 0.92;
            letter-spacing: -0.075em;
        }

        h2 {
            margin: 0 0 14px;
            font-size: 28px;
            letter-spacing: -0.04em;
        }

        h3 {
            margin: 0;
            font-size: 18px;
            letter-spacing: -0.025em;
        }

        .hero-text {
            max-width: 680px;
            margin: 24px 0 0;
            color: var(--muted);
            font-size: 18px;
            line-height: 1.7;
        }

        .formula {
            margin-top: 28px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            color: #111827;
            background: rgba(17, 24, 39, 0.06);
            border: 1px solid rgba(17, 24, 39, 0.08);
            border-radius: 18px;
            padding: 12px 14px;
        }

        .hero-side {
            display: grid;
            gap: 16px;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .metric {
            border-radius: 26px;
            padding: 22px;
        }

        .metric-label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .metric-value {
            margin-top: 10px;
            font-size: 34px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.055em;
        }

        .metric-caption {
            margin-top: 10px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.45;
        }

        .control-card {
            border-radius: 30px;
            padding: 24px;
            background: linear-gradient(135deg, rgba(17,24,39,0.94), rgba(30,41,59,0.92));
            color: white;
            box-shadow: 0 28px 70px rgba(15, 23, 42, 0.22);
        }

        .control-card .muted { color: rgba(255,255,255,0.72); }

        .controls {
            display: grid;
            grid-template-columns: 1.4fr 0.8fr auto;
            gap: 12px;
            margin-top: 18px;
            align-items: end;
        }

        label {
            display: grid;
            gap: 8px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }

        .control-card label { color: rgba(255,255,255,0.78); }

        input, select, button {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid rgba(17, 24, 39, 0.14);
            border-radius: 15px;
            background: rgba(255,255,255,0.92);
            color: var(--ink);
            font: inherit;
            outline: none;
        }

        input:focus, select:focus {
            border-color: rgba(37, 99, 235, 0.55);
            box-shadow: 0 0 0 4px rgba(37,99,235,0.10);
        }

        button, .primary-button {
            border: 0;
            color: white;
            cursor: pointer;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary), var(--violet));
            box-shadow: 0 14px 26px rgba(37, 99, 235, 0.26);
        }

        button:hover, .primary-button:hover { transform: translateY(-1px); }

        .section {
            margin-top: 24px;
        }

        .card {
            border-radius: var(--radius);
            padding: 28px;
            margin-bottom: 24px;
        }

        .section-head {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: start;
            margin-bottom: 20px;
        }

        .muted {
            color: var(--muted);
            line-height: 1.6;
        }

        .success, .error {
            padding: 14px 16px;
            border-radius: 18px;
            margin: 12px 0 18px;
            font-weight: 750;
        }

        .success { background: rgba(5,150,105,0.10); color: #047857; }
        .error { background: rgba(220,38,38,0.10); color: #b91c1c; white-space: pre-wrap; }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: rgba(255,255,255,0.55);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1080px;
        }

        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid rgba(17,24,39,0.08);
            text-align: left;
            vertical-align: middle;
            font-size: 14px;
        }

        th {
            position: sticky;
            top: 0;
            z-index: 1;
            color: #475467;
            background: rgba(248,250,252,0.92);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        tr:last-child td { border-bottom: 0; }
        td input, td select { min-width: 94px; padding: 10px 11px; border-radius: 12px; }
        td .wide-input { min-width: 210px; }

        .ranking-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(190px, 1fr));
            gap: 16px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .rank-card {
            min-width: 210px;
            border-radius: 24px;
            padding: 18px;
            box-shadow: 0 18px 48px rgba(15,23,42,0.08);
        }

        .rank-number {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: var(--primary-soft);
            color: #1d4ed8;
            font-weight: 950;
            margin-bottom: 14px;
        }

        .score-line {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 16px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }

        .bar {
            height: 9px;
            width: 100%;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(17,24,39,0.09);
            margin-top: 8px;
        }

        .bar > span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--primary), var(--violet));
        }

        .plan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 16px;
        }

        .plan-card {
            border-radius: 28px;
            padding: 22px;
            box-shadow: 0 18px 48px rgba(15,23,42,0.08);
            position: relative;
            overflow: hidden;
        }

        .plan-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 6px;
            background: linear-gradient(180deg, var(--primary), var(--violet));
        }

        .pill-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 14px 0;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            width: fit-content;
            padding: 8px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            background: rgba(17,24,39,0.07);
            color: #334155;
        }

        .high { background: rgba(220,38,38,0.10); color: var(--red); }
        .medium { background: rgba(217,119,6,0.12); color: var(--amber); }
        .low { background: rgba(5,150,105,0.10); color: var(--green); }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
        }

        .weight-row, .state-row {
            display: grid;
            grid-template-columns: 190px 1fr 70px;
            gap: 14px;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(17,24,39,0.08);
        }

        .weight-row:last-child, .state-row:last-child { border-bottom: 0; }

        .state-row { grid-template-columns: minmax(190px, 1.2fr) 1fr 80px; }

        .footer {
            text-align: center;
            color: var(--muted);
            padding: 28px 0 0;
        }

        .mobile-only { display: none; }

        @media (max-width: 980px) {
            .hero { grid-template-columns: 1fr; }
            .controls { grid-template-columns: 1fr; }
            .metric-grid { grid-template-columns: 1fr 1fr; }
            .topbar { align-items: flex-start; border-radius: 28px; flex-direction: column; }
            .nav { justify-content: flex-start; }
        }

        @media (max-width: 640px) {
            .page { width: min(100% - 20px, 1440px); padding-top: 10px; }
            .hero-panel, .card { padding: 22px; border-radius: 26px; }
            .metric-grid { grid-template-columns: 1fr; }
            .nav a { display: none; }
            h1 { font-size: 52px; }
            .weight-row, .state-row { grid-template-columns: 1fr; gap: 8px; }
        }
    </style>
</head>
<body>
<div class="page">
    <header class="topbar">
        <div class="brand">
            <div class="logo">AI</div>
            <div>
                <div>StudyHelperAI</div>
                <small class="muted">Agente inteligente de recomendación académica</small>
            </div>
        </div>
        <nav class="nav">
            <a href="#plan">Plan</a>
            <a href="#ranking">Ranking</a>
            <a href="#estado">Estado</a>
            <a href="#configuracion">Datos</a>
        </nav>
    </header>

    <main class="hero">
        <section class="hero-panel">
            <span class="eyebrow">Sistema ejecutable · Estado → decisión → plan</span>
            <h1>Estudia primero lo que más impacto tiene.</h1>
            <p class="hero-text">
                El agente analiza calificación, progreso, urgencia, impacto, carga de tareas y dificultad percibida.
                Después ordena las posibles acciones y asigna horas de estudio de forma justificada.
            </p>
            <div class="formula">
                <strong>a* = arg max f(s, a)</strong>
                <span>f = dificultad + impacto + urgencia + carga + dificultad subjetiva</span>
            </div>
        </section>

        <aside class="hero-side">
            <section class="control-card">
                <h2>Ejecutar agente</h2>
                <p class="muted"><?php echo safe(profileDescription($profile)); ?></p>
                <form method="GET" class="controls">
                    <label>
                        Perfil de decisión
                        <select name="profile">
                            <option value="balanced" <?php if ($profile === "balanced") echo "selected"; ?>>Balanceado</option>
                            <option value="urgent" <?php if ($profile === "urgent") echo "selected"; ?>>Urgencia</option>
                            <option value="low_performance" <?php if ($profile === "low_performance") echo "selected"; ?>>Bajo rendimiento</option>
                        </select>
                    </label>
                    <label>
                        Horas disponibles
                        <input type="number" name="hours" value="<?php echo safe($hours); ?>" min="1" max="12">
                    </label>
                    <button type="submit">Calcular plan</button>
                </form>
            </section>

            <section class="metric-grid">
                <article class="metric">
                    <div class="metric-label">Mejor acción</div>
                    <div class="metric-value"><?php echo $bestAction ? formatScore($bestAction["score"]) : "--"; ?></div>
                    <div class="metric-caption"><?php echo $bestAction ? safe($bestAction["subject"]) : "Sin datos del agente"; ?></div>
                </article>
                <article class="metric">
                    <div class="metric-label">Uso de tiempo</div>
                    <div class="metric-value"><?php echo $data ? formatPercent($data["metrics"]["time_usage"] * 100) : "--"; ?></div>
                    <div class="metric-caption"><?php echo $data ? formatHours($data["metrics"]["assigned_hours"]) . " asignadas" : "Sin cálculo"; ?></div>
                </article>
                <article class="metric">
                    <div class="metric-label">Temas planeados</div>
                    <div class="metric-value"><?php echo safe($plannedSubjects); ?></div>
                    <div class="metric-caption">Acciones incluidas en el plan final</div>
                </article>
                <article class="metric">
                    <div class="metric-label">Progreso medio</div>
                    <div class="metric-value"><?php echo formatPercent($averageProgress); ?></div>
                    <div class="metric-caption">Promedio del estado inicial</div>
                </article>
            </section>
        </aside>
    </main>

    <?php if ($message !== ""): ?>
        <div class="success"><?php echo safe($message); ?></div>
    <?php endif; ?>
    <?php if ($errorMessage !== ""): ?>
        <div class="error"><?php echo safe($errorMessage); ?></div>
    <?php endif; ?>

    <?php if (!$data): ?>
        <section class="card error">
            No se pudo ejecutar el agente. Revisa que Python esté instalado y que exista src/web_output.py.
            <?php if ($agentError !== ""): ?>

Salida recibida:
<?php echo safe($agentError); ?>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <section class="section" id="plan">
            <div class="section-head">
                <div>
                    <h2>Plan de estudio generado</h2>
                    <p class="muted">Estas son las acciones seleccionadas por el agente después de evaluar el estado actual.</p>
                </div>
                <span class="pill">Perfil: <?php echo safe(profileLabel($data["profile"])); ?></span>
            </div>

            <div class="plan-grid">
                <?php foreach ($data["study_plan"] as $item): ?>
                    <?php
                        $priorityClass = "low";
                        if ($item["priority"] === "Alta") {
                            $priorityClass = "high";
                        } elseif ($item["priority"] === "Media") {
                            $priorityClass = "medium";
                        }
                    ?>
                    <article class="plan-card">
                        <h3><?php echo safe($item["subject"]); ?></h3>
                        <p class="muted"><?php echo safe($item["topic"]); ?></p>
                        <div class="pill-row">
                            <span class="pill <?php echo $priorityClass; ?>">Prioridad <?php echo safe($item["priority"]); ?></span>
                            <span class="pill"><?php echo formatHours($item["assigned_hours"]); ?></span>
                            <span class="pill"><?php echo safe(styleLabel($item["learning_style"])); ?></span>
                        </div>
                        <p><strong>Actividad:</strong> <?php echo safe($item["activity"]); ?></p>
                        <p><strong>Recurso:</strong> <?php echo safe($item["resource"]); ?></p>
                        <div class="score-line"><span>Puntaje</span><span><?php echo formatScore($item["score"]); ?></span></div>
                        <div class="bar"><span style="width: <?php echo min(100, max(0, (float)$item["score"] * 100)); ?>%"></span></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="card section" id="ranking">
            <div class="section-head">
                <div>
                    <h2>Ranking de prioridades</h2>
                    <p class="muted">El ranking muestra todas las acciones posibles ordenadas de mayor a menor conveniencia.</p>
                </div>
            </div>

            <div class="ranking-grid">
                <?php foreach ($data["ranked_actions"] as $index => $action): ?>
                    <article class="rank-card">
                        <div class="rank-number"><?php echo $index + 1; ?></div>
                        <h3><?php echo safe($action["subject"]); ?></h3>
                        <p class="muted"><?php echo safe($action["topic"]); ?></p>
                        <div class="score-line"><span>Puntaje</span><span><?php echo formatScore($action["score"]); ?></span></div>
                        <div class="bar"><span style="width: <?php echo min(100, max(0, (float)$action["score"] * 100)); ?>%"></span></div>
                        <div class="pill-row">
                            <span class="pill">Urg. <?php echo formatScore($action["urgency"]); ?></span>
                            <span class="pill">Dif. <?php echo formatScore($action["difficulty"]); ?></span>
                            <span class="pill">Imp. <?php echo formatScore($action["impact"]); ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="details-grid section">
            <article class="card">
                <h2>Pesos activos</h2>
                <p class="muted">Estos pesos explican por qué el agente decidió ese orden.</p>
                <?php foreach ($data["weights"] as $factor => $weight): ?>
                    <div class="weight-row">
                        <strong><?php echo safe(factorLabel($factor)); ?></strong>
                        <div class="bar"><span style="width: <?php echo min(100, max(0, (float)$weight * 100)); ?>%"></span></div>
                        <span><?php echo safe($weight); ?></span>
                    </div>
                <?php endforeach; ?>
            </article>

            <article class="card">
                <h2>Métricas del agente</h2>
                <p class="muted">Resumen cuantitativo de la ejecución actual.</p>
                <div class="weight-row"><strong>Promedio</strong><div class="bar"><span style="width: <?php echo $data["metrics"]["average_score"] * 100; ?>%"></span></div><span><?php echo formatScore($data["metrics"]["average_score"]); ?></span></div>
                <div class="weight-row"><strong>Mejor puntaje</strong><div class="bar"><span style="width: <?php echo $data["metrics"]["best_score"] * 100; ?>%"></span></div><span><?php echo formatScore($data["metrics"]["best_score"]); ?></span></div>
                <div class="weight-row"><strong>Diferencia top 2</strong><div class="bar"><span style="width: <?php echo min(100, $data["metrics"]["score_difference"] * 100); ?>%"></span></div><span><?php echo formatScore($data["metrics"]["score_difference"]); ?></span></div>
                <div class="weight-row"><strong>Horas</strong><div class="bar"><span style="width: <?php echo min(100, $data["metrics"]["time_usage"] * 100); ?>%"></span></div><span><?php echo safe($data["metrics"]["assigned_hours"]); ?>/<?php echo safe($data["metrics"]["available_hours"]); ?></span></div>
            </article>
        </section>

        <section class="card section" id="estado">
            <div class="section-head">
                <div>
                    <h2>Estado actualizado simulado</h2>
                    <p class="muted">Después de aplicar el plan, el sistema simula una mejora de progreso según las horas asignadas.</p>
                </div>
            </div>
            <?php foreach ($data["updated_state"] as $item): ?>
                <div class="state-row">
                    <div>
                        <strong><?php echo safe($item["subject"]); ?></strong><br>
                        <span class="muted"><?php echo safe($item["topic"]); ?></span>
                    </div>
                    <div class="bar"><span style="width: <?php echo min(100, max(0, (float)$item["progress"])); ?>%"></span></div>
                    <span><?php echo formatPercent($item["progress"]); ?></span>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <section class="card section" id="configuracion">
        <div class="section-head">
            <div>
                <h2>Editar estado académico</h2>
                <p class="muted">Cambia valores, guarda y vuelve a ejecutar. La última fila vacía sirve para agregar una materia nueva.</p>
            </div>
            <button type="button" class="ghost-button" onclick="addRow()">Agregar fila</button>
        </div>

        <form method="POST">
            <div class="table-wrapper">
                <table id="dataTable">
                    <thead>
                    <tr>
                        <?php foreach ($fields as $field): ?>
                            <th><?php echo safe($fieldLabels[$field]); ?></th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $editableRows = $csvRows; $editableRows[] = []; ?>
                    <?php foreach ($editableRows as $index => $row): ?>
                        <tr>
                            <td><input class="wide-input" name="rows[<?php echo $index; ?>][subject]" value="<?php echo safe($row["subject"] ?? ""); ?>" placeholder="Materia"></td>
                            <td><input class="wide-input" name="rows[<?php echo $index; ?>][topic]" value="<?php echo safe($row["topic"] ?? ""); ?>" placeholder="Tema"></td>
                            <td><input type="number" name="rows[<?php echo $index; ?>][grade]" value="<?php echo safe($row["grade"] ?? ""); ?>" min="0" max="100" placeholder="0-100"></td>
                            <td><input type="number" name="rows[<?php echo $index; ?>][progress]" value="<?php echo safe($row["progress"] ?? ""); ?>" min="0" max="100" placeholder="0-100"></td>
                            <td><input type="number" name="rows[<?php echo $index; ?>][days_to_exam]" value="<?php echo safe($row["days_to_exam"] ?? ""); ?>" min="1" placeholder="1"></td>
                            <td><input type="number" step="0.01" name="rows[<?php echo $index; ?>][impact]" value="<?php echo safe($row["impact"] ?? ""); ?>" min="0" max="1" placeholder="0-1"></td>
                            <td><input type="number" name="rows[<?php echo $index; ?>][task_load]" value="<?php echo safe($row["task_load"] ?? ""); ?>" min="1" max="5" placeholder="1-5"></td>
                            <td><input type="number" step="0.01" name="rows[<?php echo $index; ?>][subjective_difficulty]" value="<?php echo safe($row["subjective_difficulty"] ?? ""); ?>" min="0" max="1" placeholder="0-1"></td>
                            <td><input type="number" name="rows[<?php echo $index; ?>][available_days]" value="<?php echo safe($row["available_days"] ?? ""); ?>" min="1" placeholder="1"></td>
                            <td>
                                <?php $selectedStyle = $row["learning_style"] ?? "practice"; ?>
                                <select name="rows[<?php echo $index; ?>][learning_style]">
                                    <option value="practice" <?php if ($selectedStyle === "practice") echo "selected"; ?>>Práctica</option>
                                    <option value="visual" <?php if ($selectedStyle === "visual") echo "selected"; ?>>Visual</option>
                                    <option value="reading" <?php if ($selectedStyle === "reading") echo "selected"; ?>>Lectura</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <br>
            <button type="submit" name="save_data" value="1">Guardar estado y recalcular</button>
        </form>
    </section>

    <footer class="footer">
        StudyHelperAI · Proyecto de Inteligencia Artificial · Consola + Experimentos + Web
    </footer>
</div>

<script>
let nextRowIndex = <?php echo count($editableRows); ?>;

function addRow() {
    const tbody = document.querySelector('#dataTable tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input class="wide-input" name="rows[${nextRowIndex}][subject]" placeholder="Materia"></td>
        <td><input class="wide-input" name="rows[${nextRowIndex}][topic]" placeholder="Tema"></td>
        <td><input type="number" name="rows[${nextRowIndex}][grade]" min="0" max="100" placeholder="0-100"></td>
        <td><input type="number" name="rows[${nextRowIndex}][progress]" min="0" max="100" placeholder="0-100"></td>
        <td><input type="number" name="rows[${nextRowIndex}][days_to_exam]" min="1" placeholder="1"></td>
        <td><input type="number" step="0.01" name="rows[${nextRowIndex}][impact]" min="0" max="1" placeholder="0-1"></td>
        <td><input type="number" name="rows[${nextRowIndex}][task_load]" min="1" max="5" placeholder="1-5"></td>
        <td><input type="number" step="0.01" name="rows[${nextRowIndex}][subjective_difficulty]" min="0" max="1" placeholder="0-1"></td>
        <td><input type="number" name="rows[${nextRowIndex}][available_days]" min="1" placeholder="1"></td>
        <td>
            <select name="rows[${nextRowIndex}][learning_style]">
                <option value="practice">Práctica</option>
                <option value="visual">Visual</option>
                <option value="reading">Lectura</option>
            </select>
        </td>`;
    tbody.appendChild(row);
    nextRowIndex += 1;
}
</script>
</body>
</html>
