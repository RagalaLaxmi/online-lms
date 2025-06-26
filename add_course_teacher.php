<?php
session_start();
include('db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
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
    if ($video_url !== '' && !filter_var($video_url, FILTER_VALIDATE_URL)) $errors[] = "Please enter a valid video URL.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO courses (course_name, course_description, video_path, user_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$course_name, $course_description, $video_url, $_SESSION['user_id']]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Course</title>
    <style>
        body { max-width: 600px; margin: 40px auto; font-family: Arial, sans-serif; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input[type="text"], textarea { width: 100%; padding: 10px; margin-top: 6px; }
        button { margin-top: 25px; padding: 12px; background: #2980b9; color: white; border: none; border-radius: 6px; cursor: pointer; }
        .error { background: #e74c3c; color: white; padding: 10px; border-radius: 6px; }
        .success { background: #2ecc71; color: white; padding: 10px; border-radius: 6px; }
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
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="course_name">Course Name</label>
        <input type="text" id="course_name" name="course_name" required value="<?= htmlspecialchars($_POST['course_name'] ?? '') ?>">

        <label for="course_description">Course Description</label>
        <textarea id="course_description" name="course_description" required><?= htmlspecialchars($_POST['course_description'] ?? '') ?></textarea>

        <label for="video_url">YouTube Video URL (optional)</label>
        <input type="text" id="video_url" name="video_url" placeholder="https://www.youtube.com/watch?v=VIDEO_ID" value="<?= htmlspecialchars($_POST['video_url'] ?? '') ?>">

        <button type="submit">Add Course</button>
    </form>

    <p><a href="teacher_courses.php">← Back to Your Courses</a></p>
</body>
</html>
