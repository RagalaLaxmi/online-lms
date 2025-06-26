<?php
// manage_teachers.php
session_start();
require_once 'db.php';

// Only allow admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle approve/reject actions
if (isset($_POST['action'], $_POST['teacher_id'])) {
    $teacher_id = (int)$_POST['teacher_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'reject') {
        $status = 'rejected';
    } else {
        $status = null;
    }

    if ($status) {
        $stmt = $pdo->prepare("UPDATE users SET teacher_status = :status WHERE id = :id AND role = 'teacher'");
        $stmt->execute(['status' => $status, 'id' => $teacher_id]);
    }
}

// Fetch all pending teacher registrations
$stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE role = 'teacher' AND teacher_status = 'pending'");
$stmt->execute();
$pendingTeachers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Teacher Registrations</title>
    <link rel="stylesheet" href="styles10.css" />
</head>
<body>
    <div class="sidebar">
        <h2>Admin Dashboard</h2>
        <ul>
            <li><a href="view_users.php">Manage Users</a></li>
            <li><a href="manage_teachers.php">Manage Instructors</a></li>
            <li><a href="view_courses.php">Manage Courses</a></li>
            <li><a href="view_enrollments.php">Manage Enrollments</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Pending Teacher Registrations</h1>
        <?php if (count($pendingTeachers) === 0): ?>
            <p>No pending teacher registrations at the moment.</p>
        <?php else: ?>
            <table border="1" cellpadding="10" cellspacing="0">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Registered On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingTeachers as $teacher): ?>
                    <tr>
                        <td><?= htmlspecialchars($teacher['username']) ?></td>
                        <td><?= htmlspecialchars($teacher['email']) ?></td>
                        <td><?= htmlspecialchars($teacher['created_at']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="teacher_id" value="<?= $teacher['id'] ?>">
                                <button type="submit" name="action" value="approve" onclick="return confirm('Approve this teacher?');">Approve</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="teacher_id" value="<?= $teacher['id'] ?>">
                                <button type="submit" name="action" value="reject" onclick="return confirm('Reject this teacher?');">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
