<?php
session_start();
require_once "db_connect.php";

// Ensure admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $problem_id = $_POST['problem_id'] ?? null;
    $user_id = $_POST['user_id'] ?? null;

    try {
        if ($action === 'verify' && $problem_id) {
            $pdo->prepare("UPDATE problems SET status='verified' WHERE problem_id=?")
                ->execute([$problem_id]);
            $_SESSION['flash_success'] = "Problem verified successfully!";
        }
        elseif ($action === 'reject' && $problem_id) {
            $pdo->prepare("UPDATE problems SET status='rejected' WHERE problem_id=?")
                ->execute([$problem_id]);
            $_SESSION['flash_success'] = "Problem rejected.";
        }
        elseif ($action === 'assign' && $problem_id) {
            $priority = $_POST['priority'] ?? null;
            $assigned_team_id = $_POST['assigned_team_id'] ?? null;

            if (!$priority || !$assigned_team_id) {
                throw new Exception("Missing fields for assignment.");
            }

            $stmt = $pdo->prepare("
                UPDATE problems
                SET status='assigned', priority=?, assigned_team_id=?
                WHERE problem_id=?
            ");
            $stmt->execute([$priority, $assigned_team_id, $problem_id]);

            $_SESSION['flash_success'] = "Problem assigned successfully!";
        }
        elseif ($action === 'delete' && $problem_id) {
            $pdo->prepare("DELETE FROM problems WHERE problem_id=?")
                ->execute([$problem_id]);
            $_SESSION['flash_success'] = "Problem deleted.";
        }
        elseif ($action === 'approve_team' && $user_id) {
            $pdo->prepare("UPDATE users SET status='active' WHERE user_id=?")
                ->execute([$user_id]);
            $_SESSION['flash_success'] = "Response team approved!";
        }
        elseif ($action === 'reject_team' && $user_id) {
            $pdo->prepare("DELETE FROM users WHERE user_id=?")
                ->execute([$user_id]);
            $_SESSION['flash_success'] = "Response team rejected.";
        }
    } catch (Exception $e) {
        $_SESSION['flash_error'] = "Error: " . $e->getMessage();
    }
}

header("Location: admin_dashboard.php");
exit();
?>
