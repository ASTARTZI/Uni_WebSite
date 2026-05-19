<?php
require_once "config.php";
require_once "auth.php";

/* ΜΟΝΟ Tutor */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Tutor') {
    http_response_code(403);
    exit("Access denied.");
}

$isTutor = true;
$action = $_GET['action'] ?? ''; // '', 'add', 'edit'
$errors = [];

/* DELETE */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    if ($id === (int)$_SESSION['user']['id']) {
        $errors[] = "Δεν μπορείς να διαγράψεις τον χρήστη που είναι συνδεδεμένος.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: users.php");
        exit;
    }
}

/* ADD/EDIT POST */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $login      = trim($_POST['login'] ?? '');
    $role       = trim($_POST['role'] ?? '');
    $password   = (string)($_POST['password'] ?? '');
    $id         = (int)($_POST['id'] ?? 0);

    if ($first_name === '') $errors[] = "Συμπλήρωσε όνομα.";
    if ($last_name === '')  $errors[] = "Συμπλήρωσε επώνυμο.";
    if ($login === '')      $errors[] = "Συμπλήρωσε email (login).";
    if (!filter_var($login, FILTER_VALIDATE_EMAIL)) $errors[] = "Το email δεν είναι έγκυρο.";
    if ($role !== 'Tutor' && $role !== 'Student') $errors[] = "Ο ρόλος πρέπει να είναι Tutor ή Student.";

    /* ADD */
    if (isset($_POST['do_add'])) {
        if ($password === '') $errors[] = "Στο νέο χρήστη το password είναι υποχρεωτικό.";

        if (!$errors) {
            // έλεγχος μοναδικότητας login
            $chk = $pdo->prepare("SELECT id FROM users WHERE login = ?");
            $chk->execute([$login]);
            if ($chk->fetch()) {
                $errors[] = "Υπάρχει ήδη χρήστης με αυτό το email.";
                $action = 'add';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (first_name, last_name, login, password_hash, role)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$first_name, $last_name, $login, $hash, $role]);

                header("Location: users.php");
                exit;
            }
        } else {
            $action = 'add';
        }
    }

    /* EDIT */
    if (isset($_POST['do_edit'])) {
        if ($id <= 0) {
            $errors[] = "Μη έγκυρο id χρήστη.";
            $action = '';
        } else if (!$errors) {

            // έλεγχος μοναδικότητας login (εκτός του ίδιου χρήστη)
            $chk = $pdo->prepare("SELECT id FROM users WHERE login = ? AND id <> ?");
            $chk->execute([$login, $id]);
            if ($chk->fetch()) {
                $errors[] = "Υπάρχει ήδη άλλος χρήστης με αυτό το email.";
                $action = 'edit';
            } else {

                
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET first_name = ?, last_name = ?, login = ?, password_hash = ?, role = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$first_name, $last_name, $login, $hash, $role, $id]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET first_name = ?, last_name = ?, login = ?, role = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$first_name, $last_name, $login, $role, $id]);
                }

                header("Location: users.php");
                exit;
            }
        } else {
            $action = 'edit';
        }
    }
}

/* LOAD FOR EDIT */

$editRow = null;
if ($action === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, login, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $editRow = $stmt->fetch();
        if (!$editRow) {
            header("Location: users.php");
            exit;
        }
    } else {
        header("Location: users.php");
        exit;
    }
}

/* LIST */
$rows = $pdo->query("SELECT id, first_name, last_name, login, role FROM users ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Χρήστες</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="top">

<div id="container">

    <div id="header">
        <h1>Χρήστες</h1>
    </div>

    <div id="menu">
        <a href="index.php"><img src="images/home.jpg"><span>Αρχική</span></a>
        <a href="announcements.php"><img src="images/announcements.jpg"><span>Ανακοινώσεις</span></a>
        <a href="communication.php"><img src="images/communication.jpg"><span>Επικοινωνία</span></a>
        <a href="documents.php"><img src="images/documents.jpg"><span>Έγγραφα</span></a>
        <a href="homework.php"><img src="images/homework.jpg"><span>Εργασίες</span></a>
        <a href="users.php"><img src="images/users.jpg"><span>Χρήστες</span></a>
        <a href="logout.php" style="margin-top:10px;"><img src="images/exit.jpg"><span>Έξοδος</span></a>
    </div>

    <div id="content">

        <p style="text-align:right; color:#333;">
            Συνδεδεμένος/η:
            <strong><?= htmlspecialchars($_SESSION['user']['first_name']." ".$_SESSION['user']['last_name']) ?></strong>
            (<?= htmlspecialchars($_SESSION['user']['role']) ?>)
        </p>

        <?php if ($action === ''): ?>
            <p>
                <a href="users.php?action=add"
                   style="display:inline-block; padding:8px 12px; border:1px solid #999; border-radius:4px; background:#f3f3f3; text-decoration:none;">
                    Προσθήκη νέου χρήστη
                </a>
            </p>
            <hr>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div style="border:1px solid #b00020; padding:10px; margin:10px 0;">
                <strong>Διόρθωσε:</strong>
                <ul>
                    <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- ADD FORM -->
        <?php if ($action === 'add'): ?>
            <h2>Προσθήκη νέου χρήστη</h2>

            <form method="post" action="users.php?action=add">
                <p><strong>Όνομα:</strong><br>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required style="width:100%;">
                </p>
                <p><strong>Επώνυμο:</strong><br>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required style="width:100%;">
                </p>
                <p><strong>Email (Login):</strong><br>
                    <input type="email" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required style="width:100%;">
                </p>
                <p><strong>Password:</strong><br>
                    <input type="password" name="password" required style="width:100%;">
                </p>
                <p><strong>Ρόλος:</strong><br>
                    <select name="role" required>
                        <option value="Student" <?= (($_POST['role'] ?? '') === 'Student') ? 'selected' : '' ?>>Student</option>
                        <option value="Tutor" <?= (($_POST['role'] ?? '') === 'Tutor') ? 'selected' : '' ?>>Tutor</option>
                    </select>
                </p>

                <p>
                    <button type="submit" name="do_add">Αποθήκευση</button>
                    <a href="users.php" style="margin-left:10px;">Ακύρωση</a>
                </p>
            </form>
            <hr>
        <?php endif; ?>

        <!-- EDIT FORM -->
        <?php if ($action === 'edit' && $editRow): ?>
            <h2>Επεξεργασία χρήστη #<?= (int)$editRow['id'] ?></h2>

            <form method="post" action="users.php?action=edit&id=<?= (int)$editRow['id'] ?>">
                <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">

                <p><strong>Όνομα:</strong><br>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? $editRow['first_name']) ?>" required style="width:100%;">
                </p>
                <p><strong>Επώνυμο:</strong><br>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? $editRow['last_name']) ?>" required style="width:100%;">
                </p>
                <p><strong>Email (Login):</strong><br>
                    <input type="email" name="login" value="<?= htmlspecialchars($_POST['login'] ?? $editRow['login']) ?>" required style="width:100%;">
                </p>
                <p><strong>Νέο Password (άφησέ το κενό αν δεν θες αλλαγή):</strong><br>
                    <input type="password" name="password" style="width:100%;">
                </p>
                <p><strong>Ρόλος:</strong><br>
                    <select name="role" required>
                        <option value="Student" <?= (($editRow['role'] ?? '') === 'Student') ? 'selected' : '' ?>>Student</option>
                        <option value="Tutor" <?= (($editRow['role'] ?? '') === 'Tutor') ? 'selected' : '' ?>>Tutor</option>
                    </select>
                </p>

                <p>
                    <button type="submit" name="do_edit">Αποθήκευση</button>
                    <a href="users.php" style="margin-left:10px;">Ακύρωση</a>
                </p>
            </form>
            <hr>
        <?php endif; ?>

        <!-- LIST -->
        <?php if (!$rows): ?>
            <p>Δεν υπάρχουν χρήστες.</p>
        <?php endif; ?>

        <h2>Λίστα χρηστών</h2>
        <table border="1" cellpadding="6" cellspacing="0" style="width:100%; background:white;">
            <tr>
                <th>ID</th>
                <th>Όνομα</th>
                <th>Επώνυμο</th>
                <th>Email (Login)</th>
                <th>Ρόλος</th>
                <th>Ενέργειες</th>
            </tr>
            <?php foreach ($rows as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= htmlspecialchars($u['first_name']) ?></td>
                    <td><?= htmlspecialchars($u['last_name']) ?></td>
                    <td><?= htmlspecialchars($u['login']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td>
                        <a href="users.php?action=edit&id=<?= (int)$u['id'] ?>">Επεξεργασία</a>
                        <?php if ((int)$u['id'] !== (int)$_SESSION['user']['id']): ?>
                            | <a href="users.php?delete=<?= (int)$u['id'] ?>" onclick="return confirm('Σίγουρα διαγραφή;')">Διαγραφή</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <p style="text-align:right; margin-top:15px;">
            <a href="#top">top</a>
        </p>

    </div>
</div>

</body>
</html>
