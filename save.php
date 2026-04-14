<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['hash']) || !isset($input['data'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$hash = preg_replace('/[^a-f0-9]/', '', $input['hash']);
if (strlen($hash) !== 64) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid hash']);
    exit;
}

$dir = __DIR__ . '/saves';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$file = $dir . '/' . $hash . '.json';
file_put_contents($file, json_encode($input['data']));
echo json_encode(['ok' => true]);
