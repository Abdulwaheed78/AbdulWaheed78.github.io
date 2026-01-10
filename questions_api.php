<?php
// questions_api.php

header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "interview_db");
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "DB connection failed"
    ]);
    exit;
}

$mode = $_GET['mode'] ?? '';

/* ================= CREATE / UPDATE ================= */
if ($mode === "save") {

    $data = json_decode(file_get_contents("php://input"), true);

    if (
        empty($data['title']) ||
        empty($data['language']) ||
        empty($data['difficulty'])
    ) {
        echo json_encode([
            "status" => "error",
            "message" => "Required fields missing"
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO interview_questions
        (id, language, difficulty, title, description, code)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            language = VALUES(language),
            difficulty = VALUES(difficulty),
            title = VALUES(title),
            description = VALUES(description),
            code = VALUES(code)
    ");

    $id = !empty($data['id']) ? (int) $data['id'] : null;

    $stmt->bind_param(
        "isssss",
        $id,
        $data['language'],
        $data['difficulty'],
        $data['title'],
        $data['desc'],
        $data['code']
    );

    $stmt->execute();

    echo json_encode([
        "status" => "success",
        "message" => "Question saved successfully"
    ]);
    exit;
}

/* ================= FETCH ALL ================= */
if ($mode === "list") {

    $res = $conn->query("SELECT * FROM interview_questions ORDER BY id ASC");
    $rows = [];

    while ($r = $res->fetch_assoc()) {
        $rows[] = [
            "id" => (int) $r["id"],
            "language" => $r["language"],
            "difficulty" => $r["difficulty"],
            "title" => $r["title"],
            "desc" => $r["description"],
            "code" => $r["code"]
        ];
    }

    echo json_encode($rows);
    exit;
}

/* ================= DELETE ================= */
if ($mode === "delete") {

    $id = (int) ($_GET['id'] ?? 0);

    if (!$id) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid ID"
        ]);
        exit;
    }

    $conn->query("DELETE FROM interview_questions WHERE id = $id");

    echo json_encode([
        "status" => "success",
        "message" => "Question deleted"
    ]);
    exit;
}

/* ================= EXPORT JSON & JS ================= */
if ($mode === "export") {

    $res = $conn->query("SELECT * FROM interview_questions ORDER BY id ASC");
    $questions = [];

    while ($r = $res->fetch_assoc()) {
        $questions[] = [
            "id" => (int) $r["id"],
            "language" => $r["language"],
            "difficulty" => $r["difficulty"],
            "title" => $r["title"],
            "desc" => $r["description"],
            "code" => $r["code"]
        ];
    }

    file_put_contents(
        "questions.json",
        json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    file_put_contents(
        "questions.js",
        "const QUESTIONS = " .
        json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) .
        ";"
    );

    echo json_encode([
        "status" => "success",
        "message" => "Export completed"
    ]);
    exit;
}

/* ================= CSV IMPORT ================= */
if ($mode === "import_csv") {

    if (!isset($_FILES['file'])) {
        echo json_encode([
            "status" => "error",
            "message" => "No CSV file uploaded"
        ]);
        exit;
    }

    $file = fopen($_FILES['file']['tmp_name'], "r");

    // Skip header
    fgetcsv($file);

    $inserted = 0;
    $skipped = 0;

    while (($row = fgetcsv($file)) !== false) {

        if (count($row) < 5) {
            $skipped++;
            continue;
        }

        [$language, $difficulty, $title, $description, $code] = array_map('trim', $row);

        if (!$language || !$difficulty || !$title) {
            $skipped++;
            continue;
        }
        error_log(print_r([$language, $difficulty, $title], true));

        // DUPLICATE CHECK (language + title)
        $check = $conn->prepare("
            SELECT id FROM interview_questions
            WHERE language = ? AND title = ?
            LIMIT 1
        ");
        $check->bind_param("ss", $language, $title);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $skipped++;
            continue;
        }

        $stmt = $conn->prepare("
            INSERT INTO interview_questions
            (language, difficulty, title, description, code)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssss",
            $language,
            $difficulty,
            $title,
            $description,
            $code
        );

        $stmt->execute();
        $inserted++;
    }

    fclose($file);

    echo json_encode([
        "status" => "success",
        "message" => "CSV import completed",
        "inserted" => $inserted,
        "skipped" => $skipped
    ]);
    exit;
}

/* ================= DELETE ALL ================= */
if ($mode === "delete_all") {

    $conn->query("TRUNCATE TABLE interview_questions");

    echo json_encode([
        "status" => "success",
        "message" => "All questions deleted successfully"
    ]);
    exit;
}


/* ================= INVALID MODE ================= */
echo json_encode([
    "status" => "error",
    "message" => "Invalid mode"
]);
