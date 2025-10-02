<?php

// Картинки сверху
function safe($s) {
return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function allowed_image($tmpPath, $originalName) {
$allowed_ext = ['jpg','jpeg','png','gif','webp'];
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
if (!in_array($ext, $allowed_ext, true)) return false;
$info = @getimagesize($tmpPath);
if ($info === false) return false;
return true;
}

function list_uploads() {
$files = array_filter(scandir(UPLOAD_DIR), fn($f) => !in_array($f, ['.','..']));
$files = array_map(fn($f) => UPLOAD_DIR . '/' . $f, $files);
usort($files, fn($a,$b) => filemtime($b) <=> filemtime($a));
return $files;
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Конфигурация
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('DATA_DIR', __DIR__ . '/data');

// Создаём папки, если их нет
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);

// События
$events_file = DATA_DIR . '/events.json';
if (!file_exists($events_file)) {
    $sample = [
        ['id' => 1, 'title' => 'День открытых дверей', 'date' => date('Y-m-d', strtotime('+14 days')), 'desc' => '...'],
        ['id' => 2, 'title' => 'Выставка студенческих работ', 'date' => date('Y-m-d', strtotime('+30 day')), 'desc' => '...'],
        ['id' => 3, 'title' => 'Концерт KALA$H в актовом зале', 'date' => date('Y-m-d', strtotime('+60 day')), 'desc' => 'Билеты за его счёт']
    ];
    file_put_contents($events_file, json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// читаем события
$events = json_decode(file_get_contents($events_file), true);

// --- Обработка формы контактов ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'contact') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name === '' || $message === '') {
        $error = 'Имя и сообщение обязательны.';
    } else {
        $msgFile = DATA_DIR . '/messages.json';
        $all = json_decode(file_get_contents($msgFile), true) ?: [];
        $all[] = [
            'time' => date('c'), 
            'name' => $name, 
            'email' => $email, 
            'message' => $message
        ];
        file_put_contents($msgFile, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $ok = 'Сообщение сохранено.';
    }
}

// Загрузка галереи
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $upload_error = 'Ошибка загрузки файла.';
    } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        $upload_error = 'Файл слишком большой.';
    } else {
        $name = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $allowed)) {
             $upload_error = 'Недопустимый тип файла.'; 
        } else {
            $tmp = $_FILES['image']['tmp_name'];
            $newname = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = UPLOAD_DIR . '/' . $newname;
            if (!move_uploaded_file($tmp, $dest)) {
                $upload_error = 'Не удалось сохранить файл.';
            } else {
                header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
                exit;
            }
        }
    }
}

$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Сайт</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="logo"></div>
</header>
    <nav>
        <a href="?page=home">Главная</a> |
        <a href="?page=gallery">Галерея</a> |
        <a href="?page=events">События</a> |
        <a href="?page=contact">Контакты</a> |
        <a href="?page=admin">Админ</a>
    </nav>

<?php

// Добавление главной страницы
if ($page === 'home') {
    echo "<h1>Главная</h1>";
    echo "<p>Приветствуем!</p>";
} elseif ($page === 'gallery') {
    echo "<h1>Галерея</h1>";
    $files = array_diff(scandir(UPLOAD_DIR), ['.', '..']);
    if (empty($files)) {
        echo "<p>Нет изображений</p>";
    } else {
        foreach ($files as $file) {
            echo '<div class="thumb">';
            echo '<img src="uploads/' . rawurlencode($file) . '" alt="' . htmlspecialchars($file) . '" style="width:200px;height:140px;object-fit:cover;">';
            echo '<div>' . htmlspecialchars($file) . '</div>';
            echo '</div>';
        }
    }

// Добавление событий
} elseif ($page === 'events') {
    echo "<h1>События:</h1>";
    echo "<ul>";
    if (!empty($events) && is_array($events)) { // Проверка перед циклом
        foreach ($events as $ev) {
            echo "<li><strong>".safe($ev['title'])."</strong> — ".safe($ev['date'])."<br>".safe($ev['desc'])."</li>";
        }
    } else 
    echo "</ul>";
} elseif ($page === 'contact') {
    echo "<h1>Контакты</h1>";
    if (isset($error)) echo "<p style='color:red;'>$error</p>";
    if (isset($ok)) echo "<p style='color:green;'>$ok</p>";
    ?>
<form method="post">
  <h3>Форма обратной связи</h3>
  <input type="hidden" name="action" value="contact">
  <input name="name" placeholder="Ваше имя" required>
  <input name="email" placeholder="Email">
  <textarea name="message" placeholder="Сообщение" required></textarea>
  <button type="submit">Отправить</button>
</form>
<div class="footer-section">
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i> Москва, ул. Образования, 25</li>
                        <li><i class="fas fa-phone"></i> +7 (495) 123-45-67</li>
                        <li><i class="fas fa-envelope"></i> info@collegekalash.ru</li>
                        <li><i class="fas fa-clock"></i> Пн-Пт: 9:00 - 20:00</li>
                    </ul>
                </div>
            </div>
    <?php
} elseif ($page === 'admin') {
    echo "<h1>Сообщения</h1>";
    $msgFile = DATA_DIR . '/messages.json';
    if (file_exists($msgFile)) {
        $msgs = json_decode(file_get_contents($msgFile), true);
        foreach ($msgs as $m) {
            echo "<div><strong>".safe($m['name'])."</strong> (".safe($m['email']).")<br>";
            echo safe($m['message'])."<br><small>".safe($m['time'])."</small></div><hr>";
        }
    } else {
        echo "Сообщений нет.";
    }
}
// Читка сообщений
?>
</body>
</html>

<?php
require 'db.php'; // подключаем базу

$page = $_GET['page'] ?? 'home';

?>

<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<title>Сайт</title>
<link rel="stylesheet" href="style.css">
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
nav a { margin: 0 10px; text-decoration: none; }
input, button { margin: 5px 0; padding: 5px; }
.error { color: red; }
.ok { color: green; }
</style>
</head>
<body>

<nav>
  <a href="?page=home">Главная</a> |
  <a href="?page=register">Регистрация</a>
</nav>

<?php

// Главная страница
if ($page === 'home') {
    echo "<h1>Главная страница</h1>";
    echo "<p>Добро пожаловать на Сайт!</p>";
}

// Страница регистрации
elseif ($page === 'register') {
    echo "<h1>Регистрация</h1>";
    $reg_error = $reg_ok = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
        $login = trim($_POST['login'] ?? '');
        $pass = trim($_POST['password'] ?? '');
        if ($login === '' || $pass === '') {
            $reg_error = 'Логин и пароль обязательны.';
        } else {
            // Проверяем существование логина
            $stmt = $pdo->prepare("SELECT id FROM users WHERE login=?");
            $stmt->execute([$login]);
            if ($stmt->fetch()) $reg_error = 'Такой логин уже существует.';
            else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (login,password) VALUES (?,?)");
                $stmt->execute([$login,$hash]);
                $reg_ok = 'Регистрация прошла успешно!';
            }
        }
    }

    if ($reg_error) echo '<div class="error">'.safe($reg_error).'</div>';
    if ($reg_ok) echo '<div class="ok">'.safe($reg_ok).'</div>';

    ?>

    <form method="post">
        <input type="hidden" name="action" value="register">
        <input name="login" placeholder="Логин" required><br>
        <input type="password" name="password" placeholder="Пароль" required><br>
        <button type="submit">Зарегистрироваться</button>
    </form>

    <?php
}

?>
</body>
</html>