<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Обратная связь</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);

    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $message && $phone) {
        $date = date('Y-m-d H:i:s');
        $line = "[$date] Имя: $name | Email: $email | Телефон: $phone | Сообщение: $message\n";
        file_put_contents('feedback.txt', $line, FILE_APPEND);

        // Сохраняем сообщение в сессии
        session_start();
        $_SESSION['success_message'] = "Спасибо! Ваше сообщение отправлено.";

        // Перенаправляем, чтобы избежать повторной отправки
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<p>Пожалуйста, заполните все поля корректно.</p>";
    }
}

// Показываем сообщение об успехе из сессии
session_start();
if (isset($_SESSION['success_message'])) {
    echo "<p>" . $_SESSION['success_message'] . "</p>";
    unset($_SESSION['success_message']); // Удаляем сообщение после показа
}

// Читаем данные из файла
$feedbackData = [];
if (file_exists('feedback.txt')) {
    $lines = file('feedback.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (preg_match('/\[(.*?)\] Имя: (.*?) \| Email: (.*?) \| Телефон: (.*?) \| Сообщение: (.*)/', $line, $matches)) {
            $feedbackData[] = [
                'date' => $matches[1],
                'name' => $matches[2],
                'email' => $matches[3],
                'phone' => $matches[4],
                'message' => $matches[5]
            ];
        }
    }
    $feedbackData = array_reverse($feedbackData); // Новые записи сверху
}
?>
<form method="POST">
    <label>Имя: <input type="text" name="name" required></label>
    <label>Email: <input type="email" name="email" required></label>
    <label>Телефон: <input type="tel" name="phone" placeholder="+7 (XXX) XXX-XX-XX" required></label>
    <label>Сообщение:<br><textarea name="message" required></textarea></label>
    <button type="submit">Отправить</button>
</form>

<table>
    <tr>
        <th>Дата</th>
        <th>Имя</th>
        <th>Email</th>
        <th>Телефон</th>
        <th>Сообщение</th>
    </tr>
    <?php if (!empty($feedbackData)): ?>
        <?php foreach ($feedbackData as $entry): ?>
            <tr>
                <td><?= htmlspecialchars($entry['date']) ?></td>
                <td><?= htmlspecialchars($entry['name']) ?></td>
                <td><?= htmlspecialchars($entry['email']) ?></td>
                <td><?= htmlspecialchars($entry['phone']) ?></td>
                <td><?= htmlspecialchars($entry['message']) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="5">Нет данных. Отправьте форму, чтобы они появились.</td>
        </tr>
    <?php endif; ?>
</table>
</body>
</html>