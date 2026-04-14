<?php
header('Content-Type: application/json');

$hash = preg_replace('/[^a-f0-9]/', '', $_GET['hash'] ?? '');
if (strlen($hash) !== 64) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid hash']);
    exit;
}

$file = __DIR__ . '/saves/' . $hash . '.json';
if (!file_exists($file)) {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}

echo file_get_contents($file);
