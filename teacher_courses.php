<?php
session_start();
include('db.php');

// Only allow teacher
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit;
}

// Fetch courses added by this teacher (user_id from session)
$stmt = $pdo->prepare("SELECT * FROM courses WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll();

function getYouTubeId($url) {
    parse_str(parse_url($url, PHP_URL_QUERY), $params);
    return $params['v'] ?? false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Your Courses - Teacher Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        a.add-course {
            display: inline-block;
            margin-bottom: 20px;
            background: #2980b9;
            color: white;
            padding: 10px 18px;
            border-radius: 5px;
            text-decoration: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        th {
            background-color: #2980b9;
            color: white;
            text-align: left;
        }
        td.video-cell {
            width: 200px;
            text-align: center;
        }
        .thumbnail {
            cursor: pointer;
            width: 180px;
            border-radius: 6px;
            transition: transform 0.3s;
        }
        .thumbnail:hover {
            transform: scale(1.05);
        }
        .actions a {
            margin-right: 10px;
            text-decoration: none;
            color: #2980b9;
            font-weight: 600;
        }
        .actions a.delete {
            color: #e74c3c;
        }
    </style>
</head>
<body>

<h1>Your Courses</h1>
<a class="add-course" href="add_course_teacher.php">Add New Course</a>

<table>
    <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Video</th>
        <th>Actions</th>
    </tr>
    <?php if (count($courses) === 0): ?>
    <tr><td colspan="4" style="text-align:center;">No courses found. Add your first course!</td></tr>
    <?php endif; ?>

    <?php foreach ($courses as $course): 
        $ytId = getYouTubeId($course['video_path']);
    ?>
    <tr>
        <td><?= htmlspecialchars($course['course_name']) ?></td>
        <td><?= nl2br(htmlspecialchars($course['course_description'])) ?></td>
        <td class="video-cell">
            <?php if ($ytId): ?>
                <img 
                    src="https://img.youtube.com/vi/<?= $ytId ?>/hqdefault.jpg" 
                    alt="Video thumbnail" 
                    class="thumbnail" 
                    data-video-id="<?= $ytId ?>"
                />
            <?php else: ?>
                No video
            <?php endif; ?>
        </td>
        <td class="actions">
            <a href="edit_course_teacher.php?id=<?= $course['id'] ?>">Edit</a> | 
            <a href="delete_course_teacher.php?id=<?= $course['id'] ?>" onclick="return confirm('Are you sure you want to delete this course?')" class="delete">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Optional: Add modal video player if you want video preview on click -->
<script>
    document.querySelectorAll('.thumbnail').forEach(img => {
        img.addEventListener('click', () => {
            const videoId = img.getAttribute('data-video-id');
            window.open(`https://www.youtube.com/watch?v=${videoId}`, '_blank');
        });
    });
</script>

</body>
</html>
