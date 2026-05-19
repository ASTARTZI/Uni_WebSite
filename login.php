<?php
require_once "config.php";
session_start();

if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["login"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($email === "" || $password === "") {
        $error = "Συμπλήρωσε email και κωδικό.";
    } else {

        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "Δεν βρέθηκε χρήστης.";
        } elseif ($password !== $user["password"]) {
            $error = "Λάθος κωδικός.";
        } else {
            $_SESSION["user"] = [
                "id" => $user["id"],
                "first_name" => $user["first_name"],
                "last_name" => $user["last_name"],
                "role" => $user["role"],
                "login" => $user["login"]
            ];
            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Πιστοποίηση</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="container">
    <div id="header"><h1>Πιστοποίηση</h1></div>

    <div id="content">

        <?php if ($error): ?>
            <p style="color:red;"><strong><?= htmlspecialchars($error) ?></strong></p>
        <?php endif; ?>

        <form method="post">
            <p><strong>Email</strong></p>
            <input type="email" name="login" required>

            <p><strong>Password</strong></p>
            <input type="password" name="password" required>

            <p style="margin-top:10px;">
                <button type="submit">Είσοδος</button>
            </p>
        </form>

        <p style="margin-top:15px;">
            <strong>Demo λογαριασμοί:</strong><br>
            tutor@csd.auth.gr / 1234 <br>
            student@csd.auth.gr / 1234
        </p>

    </div>
</div>
</body>
</html>
