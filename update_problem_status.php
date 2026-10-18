<?php
session_start();
require_once "db_connect.php";

// Only response team can update
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'response') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $problem_id = (int)($_POST['problem_id'] ?? 0);
    $members    = (int)($_POST['members'] ?? 0);
    $status     = $_POST['status_update'] ?? '';

    if ($problem_id <= 0 || $members <= 0 || !in_array($status, ['working','resolved'])) {
        $_SESSION['flash_error'] = "Invalid input.";
        header("Location: response_dashboard.php");
        exit();
    }

    try {
        // Insert into responses log
        $stmt = $pdo->prepare("
            INSERT INTO responses (problem_id, responder_id, response_action, status_update, accepted_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $problem_id,
            $_SESSION['user_id'],
            "Deployed {$members} member(s)",
            $status
        ]);

        // Update problem status
        $newStatus = ($status === 'working') ? 'assigned' : 'resolved';
        $stmt2 = $pdo->prepare("UPDATE problems SET status=? WHERE problem_id=?");
        $stmt2->execute([$newStatus, $problem_id]);

        $_SESSION['flash_success'] = "Problem #$problem_id updated successfully.";
    } catch (Exception $e) {
        $_SESSION['flash_error'] = "Error: " . $e->getMessage();
    }
}

header("Location: response_dashboard.php");
exit();

