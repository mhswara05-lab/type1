<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = "sql300.infinityfree.com";
$user = "if0_40094677";
$pass = "armes321";
$db   = "if0_40094677_idr";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Buat tabel jika belum ada
$createTable = "CREATE TABLE IF NOT EXISTS kuesioner (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp DATETIME,
    name VARCHAR(100),
    age INT,
    gender VARCHAR(20),
    position_level VARCHAR(50),
    job VARCHAR(100),
    industry VARCHAR(100),
    
    -- Jawaban A1
    A11_1 VARCHAR(10),
    A11_2 VARCHAR(10),
    A11_3 VARCHAR(10),
    A11_4 VARCHAR(10),
    A12_1 VARCHAR(10),
    A12_2 VARCHAR(10),
    A12_3 VARCHAR(10),
    A12_4 VARCHAR(10),
    A13_1 VARCHAR(10),
    A13_2 VARCHAR(10),
    A13_3 VARCHAR(10),
    A13_4 VARCHAR(10),
    
    -- Jawaban A2
    A21_1 VARCHAR(10),
    A21_2 VARCHAR(10),
    A21_3 VARCHAR(10),
    A22_1 VARCHAR(10),
    A22_2 VARCHAR(10),
    A22_3 VARCHAR(10),
    A23_1 VARCHAR(10),
    A23_2 VARCHAR(10),
    A23_3 VARCHAR(10),
    
    -- Jawaban A3
    A31_1 VARCHAR(10),
    A31_2 VARCHAR(10),
    A31_3 VARCHAR(10),
    A32_1 VARCHAR(10),
    A32_2 VARCHAR(10),
    A32_3 VARCHAR(10),
    A32_4 VARCHAR(10),
    A33_1 VARCHAR(10),
    A33_2 VARCHAR(10),
    A33_3 VARCHAR(10),
    
    -- Jawaban B1
    B11_1 VARCHAR(10),
    B11_2 VARCHAR(10),
    B11_3 VARCHAR(10),
    B11_4 VARCHAR(10),
    B12_1 VARCHAR(10),
    B12_2 VARCHAR(10),
    B12_3 VARCHAR(10),
    
    -- Jawaban B2
    B2_1 VARCHAR(10),
    B2_2 VARCHAR(10),
    B2_3 VARCHAR(10),
    B2_4 VARCHAR(10),
    B2_5 VARCHAR(10),
    B2_6 VARCHAR(10),
    B2_7 VARCHAR(10),
    B2_8 VARCHAR(10),
    B2_9 VARCHAR(10),
    B2_10 VARCHAR(10),
    
    -- Skor per dimensi
    score_A11 DECIMAL(5,2),
    score_A12 DECIMAL(5,2),
    score_A13 DECIMAL(5,2),
    score_A21 DECIMAL(5,2),
    score_A22 DECIMAL(5,2),
    score_A23 DECIMAL(5,2),
    score_A31 DECIMAL(5,2),
    score_A32 DECIMAL(5,2),
    score_A33 DECIMAL(5,2),
    score_B11 DECIMAL(5,2),
    score_B12 DECIMAL(5,2),
    score_B21 DECIMAL(5,2),
    score_B22 DECIMAL(5,2),
    
    -- Skor global
    global_score DECIMAL(5,2)
)";

if (!$conn->query($createTable)) {
    die("Error creating table: " . $conn->error);
}

$data = json_decode(file_get_contents('php://input'), true);

// Siapkan data untuk query
$timestamp = date('Y-m-d H:i:s');
$name = $conn->real_escape_string($data['name']);
$age = $conn->real_escape_string($data['age']);
$gender = $conn->real_escape_string($data['gender']);
$position_level = $conn->real_escape_string($data['position_level']);
$job = $conn->real_escape_string($data['job']);
$industry = $conn->real_escape_string($data['industry']);

// Siapkan array untuk jawaban A1-A3
$answers_a = array();
foreach ($data as $key => $value) {
    if (strpos($key, 'A') === 0) {
        $answers_a[$key] = $conn->real_escape_string($value);
    }
}

// Siapkan array untuk jawaban B1
$answers_b1 = array();
foreach ($data as $key => $value) {
    if (strpos($key, 'B1') === 0) {
        $answers_b1[$key] = $conn->real_escape_string($value);
    }
}

// Siapkan array untuk jawaban B2
$answers_b2 = array();
foreach ($data as $key => $value) {
    if (strpos($key, 'B2_') === 0) {
        $answers_b2[$key] = $conn->real_escape_string($value);
    }
}

// Siapkan array untuk skor
$scores = array();
foreach ($data as $key => $value) {
    if (strpos($key, 'score_') === 0) {
        $scores[$key] = $conn->real_escape_string($value);
    }
}

$global_score = $conn->real_escape_string($data['global_score']);

// Buat query INSERT
$sql = "INSERT INTO kuesioner (
    timestamp, name, age, gender, position_level, job, industry,
    " . implode(", ", array_keys($answers_a)) . ",
    " . implode(", ", array_keys($answers_b1)) . ",
    " . implode(", ", array_keys($answers_b2)) . ",
    " . implode(", ", array_keys($scores)) . ",
    global_score
) VALUES (
    '$timestamp', '$name', '$age', '$gender', '$position_level', '$job', '$industry',
    '" . implode("', '", $answers_a) . "',
    '" . implode("', '", $answers_b1) . "',
    '" . implode("', '", $answers_b2) . "',
    '" . implode("', '", $scores) . "',
    '$global_score'
)";

// Debug log
$debug = [
    'received_data' => $data,
    'answers_a' => $answers_a,
    'answers_b1' => $answers_b1,
    'answers_b2' => $answers_b2,
    'scores' => $scores,
    'sql' => $sql
];

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Data berhasil disimpan',
        'debug' => $debug
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => $conn->error,
        'debug' => $debug
    ]);
}

$conn->close();
?>
