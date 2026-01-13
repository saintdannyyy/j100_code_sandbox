<?php
// api/execute.php - Code Execution API using Piston

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->language) || empty($data->code)) {
    http_response_code(400);
    echo json_encode(["error" => "Language and code are required"]);
    exit();
}

// Map our language names to Piston API language names and versions
$languageMap = [
    'python' => ['language' => 'python', 'version' => '3.10.0'],
    'javascript' => ['language' => 'javascript', 'version' => '18.15.0'],
    'cpp' => ['language' => 'cpp', 'version' => '10.2.0'],
    'java' => ['language' => 'java', 'version' => '15.0.2'],
    'c' => ['language' => 'c', 'version' => '10.2.0'],
    'csharp' => ['language' => 'csharp', 'version' => '6.12.0'],
    'php' => ['language' => 'php', 'version' => '8.2.3'],
    'ruby' => ['language' => 'ruby', 'version' => '3.0.1'],
    'go' => ['language' => 'go', 'version' => '1.16.2'],
    'rust' => ['language' => 'rust', 'version' => '1.68.2'],
    'typescript' => ['language' => 'typescript', 'version' => '5.0.3'],
    'sql' => null, // SQL can't be executed via Piston
    'html' => null, // HTML runs in browser
    'css' => null,  // CSS runs in browser
];

$lang = strtolower($data->language);

// Handle non-executable languages
if (!isset($languageMap[$lang]) || $languageMap[$lang] === null) {
    if ($lang === 'sql') {
        echo json_encode([
            "success" => true,
            "output" => "SQL queries cannot be executed in sandbox mode.\nUse a database client to run SQL commands.",
            "execution_time" => 0
        ]);
        exit();
    }
    if ($lang === 'html' || $lang === 'css') {
        echo json_encode([
            "success" => true,
            "output" => "HTML/CSS code runs in the browser preview.\nEnable Web Mode and click the Preview button.",
            "execution_time" => 0
        ]);
        exit();
    }
    http_response_code(400);
    echo json_encode(["error" => "Language not supported for execution: " . $lang]);
    exit();
}

$pistonLang = $languageMap[$lang];

// Prepare the request for Piston API
$pistonPayload = [
    'language' => $pistonLang['language'],
    'version' => $pistonLang['version'],
    'files' => [
        [
            'name' => getFilename($lang),
            'content' => $data->code
        ]
    ],
    'stdin' => isset($data->stdin) ? $data->stdin : '',
    'args' => isset($data->args) ? $data->args : [],
    'compile_timeout' => 10000,
    'run_timeout' => 5000,
    'compile_memory_limit' => -1,
    'run_memory_limit' => -1
];

// Call Piston API (public instance)
$pistonUrl = 'https://emkc.org/api/v2/piston/execute';

$ch = curl_init($pistonUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($pistonPayload),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode([
        "error" => "Execution service unavailable",
        "details" => $curlError
    ]);
    exit();
}

if ($httpCode !== 200) {
    http_response_code(502);
    echo json_encode([
        "error" => "Code execution failed",
        "details" => "External service returned HTTP $httpCode"
    ]);
    exit();
}

$result = json_decode($response, true);

if (isset($result['message'])) {
    // Piston returned an error message
    http_response_code(400);
    echo json_encode([
        "error" => $result['message']
    ]);
    exit();
}

// Format output
$output = '';
$hasError = false;

// Check compile stage
if (isset($result['compile']) && !empty($result['compile']['stderr'])) {
    $output .= "Compilation Error:\n" . $result['compile']['stderr'];
    $hasError = true;
}

// Check run stage
if (isset($result['run'])) {
    if (!empty($result['run']['stdout'])) {
        $output .= $result['run']['stdout'];
    }
    if (!empty($result['run']['stderr'])) {
        if (!empty($output)) $output .= "\n";
        $output .= $result['run']['stderr'];
        $hasError = true;
    }
    if (isset($result['run']['code']) && $result['run']['code'] !== 0) {
        $hasError = true;
        if (empty($output)) {
            $output = "Process exited with code " . $result['run']['code'];
        }
    }
    if (isset($result['run']['signal']) && $result['run']['signal'] !== null) {
        $output .= "\nProcess terminated by signal: " . $result['run']['signal'];
        $hasError = true;
    }
}

if (empty(trim($output))) {
    $output = "(No output)";
}

echo json_encode([
    "success" => !$hasError,
    "output" => $output,
    "language" => $result['language'] ?? $lang,
    "version" => $result['version'] ?? $pistonLang['version'],
    "execution_time" => $result['run']['time'] ?? null
]);

// Helper function to get appropriate filename
function getFilename($lang)
{
    $filenames = [
        'python' => 'main.py',
        'javascript' => 'main.js',
        'cpp' => 'main.cpp',
        'java' => 'Main.java',
        'c' => 'main.c',
        'csharp' => 'Main.cs',
        'php' => 'main.php',
        'ruby' => 'main.rb',
        'go' => 'main.go',
        'rust' => 'main.rs',
        'typescript' => 'main.ts'
    ];
    return $filenames[$lang] ?? 'main.txt';
}
