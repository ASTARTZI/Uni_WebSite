<?php
require_once "config.php";
require_once "auth.php";

$isTutor = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'Tutor';
$action = $_GET['action'] ?? '';
$errors = [];

/* DELETE */
if ($isTutor && isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: documents.php");
    exit;
}

/* ADD/EDIT POST */
if ($isTutor && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $file_path = trim($_POST['file_path'] ?? '');
    $id = (int)($_POST['id'] ?? 0);

    if ($title === '') $errors[] = "Συμπλήρωσε τίτλο.";
    if ($description === '') $errors[] = "Συμπλήρωσε περιγραφή.";
    if ($file_path === '') $errors[] = "Συμπλήρωσε όνομα/θέση αρχείου.";

    if (!$errors) {
        if (isset($_POST['do_add'])) {
            $stmt = $pdo->prepare("INSERT INTO documents (title, description, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$title, $description, $file_path]);
            header("Location: documents.php");
            exit;
        }
        if (isset($_POST['do_edit']) && $id > 0) {
            $stmt = $pdo->prepare("UPDATE documents SET title = ?, description = ?, file_path = ? WHERE id = ?");
            $stmt->execute([$title, $description, $file_path, $id]);
            header("Location: documents.php");
            exit;
        }
    } else {
        if (isset($_POST['do_add'])) $action = 'add';
        if (isset($_POST['do_edit'])) $action = 'edit';
    }
}

/* LOAD FOR EDIT */
$editRow = null;
if ($isTutor && $action === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
        $stmt->execute([$id]);
        $editRow = $stmt->fetch();
        if (!$editRow) {
            header("Location: documents.php");
            exit;
        }
    } else {
        header("Location: documents.php");
        exit;
    }
}

/* LIST */
$rows = $pdo->query("SELECT * FROM documents ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Έγγραφα</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="top">
<div id="container">

    <div id="header"><h1>Έγγραφα Μαθήματος</h1></div>

    <div id="menu">
        <a href="index.php"><img src="images/home.jpg"><span>Αρχική</span></a>
        <a href="announcements.php"><img src="images/announcements.jpg"><span>Ανακοινώσεις</span></a>
        <a href="communication.php"><img src="images/communication.jpg"><span>Επικοινωνία</span></a>
        <a href="documents.php"><img src="images/documents.jpg"><span>Έγγραφα</span></a>
        <a href="homework.php"><img src="images/homework.jpg"><span>Εργασίες</span></a>
        <?php if ($isTutor): ?>
            <a href="users.php"><img src="images/users.jpg"><span>Χρήστες</span></a>
        <?php endif; ?>
        <a href="logout.php" style="margin-top:10px;"><img src="images/exit.jpg"><span>Έξοδος</span></a>
    </div>

    <div id="content">

        <p style="text-align:right; color:#333;">
            Συνδεδεμένος/η:
            <strong><?= htmlspecialchars($_SESSION['user']['first_name']." ".$_SESSION['user']['last_name']) ?></strong>
            (<?= htmlspecialchars($_SESSION['user']['role']) ?>)
        </p>

        <?php if ($isTutor && $action === ''): ?>
            <p>
                <a href="documents.php?action=add"
                   style="display:inline-block; padding:8px 12px; border:1px solid #999; border-radius:4px; background:#f3f3f3; text-decoration:none;">
                    Προσθήκη νέου εγγράφου
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

        <?php if ($isTutor && $action === 'add'): ?>
            <h2>Προσθήκη νέου εγγράφου</h2>
            <form method="post" action="documents.php?action=add">
                <p><strong>Τίτλος:</strong><br>
                    <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required style="width:100%;">
                </p>
                <p><strong>Περιγραφή:</strong><br>
                    <textarea name="description" rows="5" required style="width:100%;"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </p>
                <p><strong>Όνομα/θέση αρχείου:</strong><br>
                    <input type="text" name="file_path" value="<?= htmlspecialchars($_POST['file_path'] ?? '') ?>" required style="width:100%;">
                </p>
                <p>
                    <button type="submit" name="do_add">Αποθήκευση</button>
                    <a href="documents.php" style="margin-left:10px;">Ακύρωση</a>
                </p>
            </form>
            <hr>
        <?php endif; ?>

        <?php if ($isTutor && $action === 'edit' && $editRow): ?>
            <h2>Επεξεργασία εγγράφου #<?= (int)$editRow['id'] ?></h2>
            <form method="post" action="documents.php?action=edit&id=<?= (int)$editRow['id'] ?>">
                <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">

                <p><strong>Τίτλος:</strong><br>
                    <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? $editRow['title']) ?>" required style="width:100%;">
                </p>
                <p><strong>Περιγραφή:</strong><br>
                    <textarea name="description" rows="5" required style="width:100%;"><?= htmlspecialchars($_POST['description'] ?? $editRow['description']) ?></textarea>
                </p>
                <p><strong>Όνομα/θέση αρχείου:</strong><br>
                    <input type="text" name="file_path" value="<?= htmlspecialchars($_POST['file_path'] ?? $editRow['file_path']) ?>" required style="width:100%;">
                </p>

                <p>
                    <button type="submit" name="do_edit">Αποθήκευση</button>
                    <a href="documents.php" style="margin-left:10px;">Ακύρωση</a>
                </p>
            </form>
            <hr>
        <?php endif; ?>

        <?php if (!$rows): ?>
            <p>Δεν υπάρχουν έγγραφα.</p>
        <?php endif; ?>

        <?php foreach ($rows as $r): ?>
            <h2><?= htmlspecialchars($r['title']) ?></h2>
            <p><strong>Περιγραφή:</strong> <?= nl2br(htmlspecialchars($r['description'])) ?></p>
            <p><a href="<?= htmlspecialchars($r['file_path']) ?>">Download</a></p>

            <?php if ($isTutor): ?>
                <p>
                    <a href="documents.php?action=edit&id=<?= (int)$r['id'] ?>">Επεξεργασία</a> |
                    <a href="documents.php?delete=<?= (int)$r['id'] ?>" onclick="return confirm('Σίγουρα διαγραφή;')">Διαγραφή</a>
                </p>
            <?php endif; ?>
            <hr>
        <?php endforeach; ?>
		
		<p style="text-align:right;">
    <a href="#top">top</a>
</p>


    </div>
</div>
</body>
</html>
