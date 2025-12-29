<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../../core/functions.php';

if (!isLoggedIn()) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$id = $_GET['id'] ?? '';
$deposits = readJSON('deposits');
$users = readJSON('users');

foreach ($deposits as $d) {
    if ((string)($d['id'] ?? '') === (string)$id) {
        $status = $d['status'] ?? 'pending';
        
        // Check if expired
        $expires_at = $d['expires_at'] ?? '';
        if ($expires_at && strtotime($expires_at) < time() && $status === 'pending') {
            $status = 'expired';
        }
        
        $new_balance = '0';
        foreach ($users as $u) {
            if ($u['id'] === $_SESSION['user_id']) {
                $new_balance = formatMoney($u['balance'] ?? 0);
                break;
            }
        }
        
        ob_end_clean();
        echo json_encode([
            'status' => $status,
            'new_balance' => $new_balance
        ]);
        exit;
    }
}

ob_end_clean();
echo json_encode(['status' => 'not_found']);
