<?php
session_start();
include('db.php');

// Check admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = trim($_POST['course_name'] ?? '');
    $course_description = trim($_POST['course_description'] ?? '');
    $video_url = trim($_POST['video_url'] ?? '');

    if ($course_name === '') $errors[] = "Course name is required.";
    if ($course_description === '') $errors[] = "Course description is required.";
    if ($video_url && !filter_var($video_url, FILTER_VALIDATE_URL)) $errors[] = "Invalid video URL.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO courses (course_name, course_description, video_path, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$course_name, $course_description, $video_url]);
        header('Location: add_course.php?success=1');
        exit;
    }
}

$success = isset($_GET['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Course</title>
    <style>
        body { max-width: 600px; margin: 40px auto; font-family: Arial; background: #f7f9fc; color: #333; }
        form { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        input[type="text"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; margin-top: 8px; }
        button { margin-top: 20px; background: #2980b9; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; width: 100%; }
        .error, .success { padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .error { background: #e74c3c; color: white; }
        .success { background: #2ecc71; color: white; }
    </style>
</head>
<body>

<h1>Add New Course</h1>

<?php if ($success): ?>
    <div class="success">Course added successfully!</div>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="error">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST">
    <label>Course Name</label>
    <input type="text" name="course_name" required value="<?= htmlspecialchars($_POST['course_name'] ?? '') ?>">

    <label>Description</label>
    <textarea name="course_description" required><?= htmlspecialchars($_POST['course_description'] ?? '') ?></textarea>

    <label>YouTube Video URL (optional)</label>
    <input type="text" name="video_url" placeholder="https://www.youtube.com/watch?v=VIDEO_ID" value="<?= htmlspecialchars($_POST['video_url'] ?? '') ?>">

    <button type="submit">Add Course</button>
</form>

<p><a href="view_courses.php">← Back to Course List</a></p>
</body>
</html>
