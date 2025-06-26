<?php
session_start();
include('db.php');

// Admin check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$courses = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC")->fetchAll();

// Robust YouTube ID extractor
function getYouTubeId($url) {
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|v\/))([^?&"\'<> #]+)/', $url, $matches)) {
        return $matches[1];
    }
    return false;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin: All Courses</title>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 30px auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border: 1px solid #ccc; }
        th { background: #2980b9; color: white; }
        .thumbnail { width: 180px; cursor: pointer; border-radius: 6px; }
        .actions a { margin-right: 10px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); }
        .modal-content { position: relative; margin: 5% auto; width: 80%; max-width: 720px; background: white; padding: 10px; border-radius: 10px; }
        .modal-content iframe { width: 100%; height: 400px; border: none; border-radius: 8px; }
        .close-btn { position: absolute; right: 15px; top: 10px; font-size: 24px; cursor: pointer; }
    </style>
</head>
<body>

<h1>Admin: All Courses</h1>
<p><a href="add_course.php" style="background:#2980b9; color:#fff; padding:10px 15px; border-radius:5px; text-decoration:none;">+ Add Course</a></p>

<table>
    <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Video</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($courses as $c): 
        $ytId = getYouTubeId($c['video_path']);
    ?>
    <tr>
        <td><?= htmlspecialchars($c['course_name']) ?></td>
        <td><?= nl2br(htmlspecialchars($c['course_description'])) ?></td>
        <td>
            <?php if ($ytId): ?>
                <img class="thumbnail" src="https://img.youtube.com/vi/<?= $ytId ?>/hqdefault.jpg" data-video-id="<?= $ytId ?>" />
            <?php else: ?>
                No video
            <?php endif; ?>
        </td>
        <td class="actions">
            <a href="edit_course.php?id=<?= $c['id'] ?>">Edit</a>
            <a href="delete_course.php?id=<?= $c['id'] ?>" onclick="return confirm('Delete this course?')" style="color:red;">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Video Modal -->
<div id="videoModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <iframe id="videoFrame" src="" allowfullscreen></iframe>
    </div>
</div>

<script>
const modal = document.getElementById('videoModal');
const iframe = document.getElementById('videoFrame');
const closeBtn = document.querySelector('.close-btn');

document.querySelectorAll('.thumbnail').forEach(thumbnail => {
    thumbnail.addEventListener('click', () => {
        const videoId = thumbnail.getAttribute('data-video-id');
        iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
        modal.style.display = 'block';
    });
});

closeBtn.onclick = () => {
    modal.style.display = 'none';
    iframe.src = '';
};

window.onclick = (e) => {
    if (e.target === modal) {
        modal.style.display = 'none';
        iframe.src = '';
    }
};
</script>

</body>
</html>
