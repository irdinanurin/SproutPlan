<?php session_start(); 
$file = 'schedule.json'; $schedule = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
$profileFile = 'profile.json';
$profile = file_exists($profileFile) ? json_decode(file_get_contents($profileFile), true) : ['name' => 'Nurin irdina', 'img' => 'https://i.pravatar.cc/150?u=nurin'];
if(isset($_GET['mark_week'])){ $id = $_GET['id']; $week = $_GET['week']; $status = $_GET['status']; foreach($schedule as &$s){ if($s['id'] == $id){ if(!isset($s['attendance_history'])) $s['attendance_history'] = []; if($status == 'r') unset($s['attendance_history'][$week]); else $s['attendance_history'][$week] = $status; break; } } file_put_contents($file, json_encode(array_values($schedule))); header("Location: schedule.php"); exit(); }
if(isset($_POST['save_class'])){ $id = $_POST['class_id']; $updated = ['id' => $id ?: uniqid(), 'day' => $_POST['day'], 'start_time' => $_POST['start_time'], 'end_time' => $_POST['end_time'], 'task' => $_POST['task'], 'location' => $_POST['location'], 'attendance_history' => json_decode($_POST['attendance_history'] ?? '[]', true)]; if($id){ foreach($schedule as &$s){ if($s['id']==$id) $s=$updated; } } else { $schedule[]=$updated; } file_put_contents($file, json_encode(array_values($schedule))); header("Location: schedule.php"); exit(); }
if(isset($_GET['delete'])){ $schedule = array_filter($schedule, function($s){return $s['id']!==$_GET['delete'];}); file_put_contents($file, json_encode(array_values($schedule))); header("Location: schedule.php"); exit(); }
$edit = ['id'=>'','day'=>'','start_time'=>'','end_time'=>'','task'=>'','location'=>'','attendance_history'=>[]]; if(isset($_GET['edit'])){ foreach($schedule as $s){ if($s['id']==$_GET['edit']) $edit=$s; } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Schedule | SproutPlan</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        html, body { margin: 0; padding: 0; height: 100vh; width: 100vw; overflow: hidden; background: #fdf6e9; font-family: 'Outfit', sans-serif; }
        .app-container { display: grid !important; grid-template-columns: 200px 1fr !important; height: 100vh; width: 100vw; }
        .sidebar { background: #111 !important; padding: 50px 15px 20px 15px !important; display: flex !important; flex-direction: column !important; align-items: center !important; height: 100vh !important; width: 200px !important; box-sizing: border-box !important; }
        .user-profile { text-align: center !important; margin-bottom: 20px !important; width: 100%; }
        .user-profile img { width: 110px !important; height: 110px !important; border-radius: 25px !important; object-fit: cover; border: 3px solid #333; margin-bottom: 8px; }
        .user-name { font-size: 1.05rem !important; font-weight: 700; color: white !important; display: block; margin: 0; }
        .nav-link { color: #888; text-decoration: none; padding: 10px 15px; border-radius: 12px; margin-bottom: 4px; font-size: 13px; display: flex; align-items: center; gap: 10px; width: 100%; box-sizing: border-box; }
        .nav-link.active { background: #222; color: white; }
        .logout-btn { color: #ff7eb3 !important; margin-top: 5px !important; }
        .main-area { padding: 15px 25px !important; display: flex !important; flex-direction: column !important; height: 100vh !important; box-sizing: border-box !important; gap: 6px !important; overflow: hidden !important; }
        .att-zone { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 6px !important; flex-shrink: 0 !important; }
        .att-row { background: white !important; padding: 6px 12px !important; border-radius: 15px !important; border: 1px solid #eee !important; display: flex !important; flex-direction: column !important; }
        .att-row span { font-size: 9px !important; font-weight: 800 !important; color: #111 !important; line-height: 1 !important; }
        .week-box { margin-top: 3px !important; display: flex !important; gap: 2px !important; flex-wrap: wrap !important; }
        .w-dot { width: 13px !important; height: 13px !important; border-radius: 3px !important; font-size: 6.5px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; text-decoration: none !important; background: #f0f0f0 !important; color: #999 !important; font-weight: 700 !important; }
        .w-dot.p { background: #8b5cf6 !important; color: white !important; }
        .w-dot.a { background: #ff4d4d !important; color: white !important; }
        .form-mini { display: grid !important; grid-template-columns: 100px 90px 90px 1fr 1fr 70px !important; gap: 6px !important; padding: 10px !important; background: white !important; border-radius: 15px !important; border: 1px solid #eee !important; flex-shrink: 0 !important; }
        .form-mini input, .form-mini select { padding: 6px !important; border-radius: 8px !important; border: 1px solid #eee !important; font-size: 11px !important; width: 100% !important; box-sizing: border-box !important; font-family: 'Outfit'; }
        .save-btn { background: #111 !important; color: white !important; border-radius: 10px !important; border: none !important; padding: 0 !important; font-weight: 700 !important; cursor: pointer !important; height: 28px !important; font-size: 11px !important; }
        .tt-grid { display: grid !important; grid-template-columns: repeat(5, 1fr) !important; gap: 10px !important; margin-top: 5px !important; flex-grow: 1 !important; overflow: hidden !important; }
        .day-lbl { font-size: 10px !important; font-weight: 800 !important; color: #8b5cf6 !important; text-align: center !important; text-transform: uppercase !important; margin-bottom: 6px !important; display: block !important; }
        .c-card { background: white !important; padding: 8px 10px !important; border-radius: 15px !important; border: 1px solid #eee !important; border-left: 4px solid #8b5cf6 !important; margin-bottom: 6px !important; position: relative !important; }
        .c-card b { font-size: 8px !important; color: #888 !important; display: block; }
        .c-card span { font-size: 10px !important; font-weight: 700 !important; color: #111 !important; display: block; margin: 1px 0 !important; }
        .c-card small { font-size: 8px !important; color: #999 !important; display: block; }
        .card-actions { position: absolute !important; top: 4px !important; right: 6px !important; display: flex !important; gap: 4px !important; }
    </style>
</head><meta name="viewport" content="width=device-width, initial-scale=1.0">
<body>
<div class="app-container">
    <aside class="sidebar">
        <div class="user-profile">
            <img src="<?= $profile['img'] ?>" alt="Profile" id="profileImage">
            <span class="user-name"><?= $profile['name'] ?></span>
            <label for="file-upload" style="color:var(--accent); cursor:pointer; font-size:10px; font-weight:bold; margin-top:5px; display:block;">EDIT PROFILE</label>
            <input id="file-upload" type="file" accept="image/*" style="display:none;">
        </div>
        <nav style="width: 100%;">
            <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
            <a href="schedule.php" class="nav-link active">📅 Schedule</a>
            <a href="homework.php" class="nav-link">📖 Homework</a>
            <a href="logout.php" class="nav-link logout-btn">🚪 Logout</a>
        </nav>
    </aside>
    <main class="main-area">
        <header style="display:flex; justify-content:space-between; align-items:center; flex-shrink: 0;">
            <h2 style="margin:0; font-size: 20px;">My Schedule & Attendance 📅</h2>
        </header>
        <div class="att-zone">
            <?php $unique = []; foreach($schedule as $s) if(!in_array($s['task'] ?? '', array_column($unique, 'task'))) $unique[] = $s; foreach(array_slice($unique, 0, 8) as $u): $history = $u['attendance_history'] ?? []; ?>
            <div class="att-row"><span><?= htmlspecialchars($u['task']) ?></span><div class="week-box"><?php for($w=1; $w<=14; $w++): $status = $history[$w] ?? 'r'; $next = ($status == 'r') ? 'p' : (($status == 'p') ? 'a' : 'r'); ?><a href="schedule.php?mark_week=1&id=<?= $u['id']; ?>&week=<?= $w; ?>&status=<?= $next; ?>" class="w-dot <?= $status; ?>"><?= $w; ?></a><?php endfor; ?></div></div>
            <?php endforeach; ?>
        </div>
        <form method="POST" class="form-mini">
            <input type="hidden" name="class_id" value="<?= $edit['id']; ?>"><input type="hidden" name="attendance_history" value='<?= json_encode($edit['attendance_history']); ?>'>
            <select name="day"><?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday'] as $d) echo "<option ".($edit['day']==$d?'selected':'').">$d</option>"; ?></select>
            <input type="time" name="start_time" value="<?= $edit['start_time']; ?>" required><input type="time" name="end_time" value="<?= $edit['end_time']; ?>" required>
            <input type="text" name="task" placeholder="Course" value="<?= $edit['task']; ?>" required><input type="text" name="location" placeholder="Room" value="<?= $edit['location']; ?>" required>
            <button type="submit" name="save_class" class="save-btn">Save</button>
        </form>
        <div class="tt-grid">
            <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day): ?><div class="day-col"><span class="day-lbl"><?= substr($day, 0, 3) ?></span><?php foreach($schedule as $s): if(($s['day']??'') == $day): ?><div class="c-card"><b><?= $s['start_time']; ?> - <?= $s['end_time']; ?></b><span><?= htmlspecialchars($s['task']); ?></span><small>📍 <?= htmlspecialchars($s['location']); ?></small><div class="card-actions"><a href="schedule.php?edit=<?= $s['id']; ?>" style="text-decoration:none; font-size:10px;">✏️</a><a href="schedule.php?delete=<?= $s['id']; ?>" style="text-decoration:none; font-size:10px;" onclick="return confirm('Delete?')">❌</a></div></div><?php endif; endforeach; ?></div><?php endforeach; ?>
        </div>
    </main>
</div>
<script>
    const imgInput = document.getElementById('file-upload');
    const profileImg = document.getElementById('profileImage');
    if(localStorage.getItem('userProfileImg')) profileImg.src = localStorage.getItem('userProfileImg');
    imgInput.addEventListener('change', function() { const reader = new FileReader(); reader.onload = function(e) { profileImg.src = e.target.result; localStorage.setItem('userProfileImg', e.target.result); }; reader.readAsDataURL(this.files[0]); });
</script>
</body>
</html>
</body>
</html>