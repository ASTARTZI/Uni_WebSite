<?php
require_once "config.php";
require_once "auth.php";

$isTutor = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'Tutor';

$action = $_GET['action'] ?? ''; // '', 'add', 'edit'
$errors = [];
$msg = "";

/* DELETE (Tutor only)*/

if ($isTutor && isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: announcements.php");
    exit;
}

/* HANDLE ADD/EDIT POST */

if ($isTutor && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $date  = trim($_POST['date'] ?? '');
    $topic = trim($_POST['topic'] ?? '');
    $body  = trim($_POST['body'] ?? '');
    $id    = (int)($_POST['id'] ?? 0);

    if ($date === '')  $errors[] = "Συμπλήρωσε ημερομηνία.";
    if ($topic === '') $errors[] = "Συμπλήρωσε θέμα.";
    if ($body === '')  $errors[] = "Συμπλήρωσε κείμενο.";

    if (!$errors) {
        if (isset($_POST['do_add'])) {
            // ADD
            $stmt = $pdo->prepare("INSERT INTO announcements (`date`, topic, body) VALUES (?, ?, ?)");
            $stmt->execute([$date, $topic, $body]);
            header("Location: announcements.php");
            exit;
        }

        if (isset($_POST['do_edit']) && $id > 0) {
            // EDIT
            $stmt = $pdo->prepare("UPDATE announcements SET `date` = ?, topic = ?, body = ? WHERE id = ?");
            $stmt->execute([$date, $topic, $body, $id]);
            header("Location: announcements.php");
            exit;
        }
    } else {
        
        if (isset($_POST['do_add'])) $action = 'add';
        if (isset($_POST['do_edit'])) $action = 'edit';
    }
}

/* LOAD row for edit form */
$editRow = null;
if ($isTutor && $action === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT id, `date`, topic, body FROM announcements WHERE id = ?");
        $stmt->execute([$id]);
        $editRow = $stmt->fetch();
        if (!$editRow) {
            header("Location: announcements.php");
            exit;
        }
    } else {
        header("Location: announcements.php");
        exit;
    }
}

/* LIST announcements */
$stmt = $pdo->query("SELECT id, `date`, topic, body FROM announcements ORDER BY id DESC");
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Ανακοινώσεις</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="top">

<div id="container">

    <div id="header">
        <h1>Ανακοινώσεις</h1>
    </div>

    <div id="menu">
        <a href="index.php"><img src="images/home.jpg" alt="Αρχική"><span>Αρχική</span></a>
        <a href="announcements.php"><img src="images/announcements.jpg" alt="Ανακοινώσεις"><span>Ανακοινώσεις</span></a>
        <a href="communication.php"><img src="images/communication.jpg" alt="Επικοινωνία"><span>Επικοινωνία</span></a>
        <a href="documents.php"><img src="images/documents.jpg" alt="Έγγραφα"><span>Έγγραφα</span></a>
        <a href="homework.php"><img src="images/homework.jpg" alt="Εργασίες"><span>Εργασίες</span></a>

        <?php if ($isTutor): ?>
            <a href="users.php"><img src="images/users.jpg" alt="Χρήστες"><span>Χρήστες</span></a>
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
                <a href="announcements.php?action=add"
                   style="display:inline-block; padding:8px 12px; border:1px solid #999; border-radius:4px; background:#f3f3f3; text-decoration:none;">
                    Προσθήκη νέας ανακοίνωσης
                </a>
            </p>
            <hr>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div style="border:1px solid #b00020; padding:10px; margin:10px 0;">
                <strong>Διόρθωσε:</strong>
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- ADD FORM (only when clicked) -->
		
        <?php if ($isTutor && $action === 'add'): ?>
            <h2>Προσθήκη νέας ανακοίνωσης</h2>

            <form method="post" action="announcements.php?action=add">
                <p>
                    <label><strong>Ημερομηνία:</strong></label><br>
                    <input type="date" name="date" value="<?= htmlspecialchars($_POST['date'] ?? '') ?>" required>
                </p>

                <p>
                    <label><strong>Θέμα:</strong></label><br>
                    <input type="text" name="topic" value="<?= htmlspecialchars($_POST['topic'] ?? '') ?>" required style="width:100%;">
                </p>

                <p>
                    <label><strong>Κυρίως κείμενο:</strong></label><br>
                    <textarea name="body" rows="7" required style="width:100%;"><?= htmlspecialchars($_POST['body'] ?? '') ?></textarea>
                </p>

                <p>
                    <button type="submit" name="do_add">Αποθήκευση</button>
                    <a href="announcements.php" style="margin-left:10px;">Ακύρωση</a>
                </p>
            </form>

            <hr>
        <?php endif; ?>

        <!-- EDIT FORM (only when clicked)--> 
		
        <?php if ($isTutor && $action === 'edit' && $editRow): ?>
            <h2>Επεξεργασία ανακοίνωσης #<?= (int)$editRow['id'] ?></h2>

            <form method="post" action="announcements.php?action=edit&id=<?= (int)$editRow['id'] ?>">
                <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">

                <p>
                    <label><strong>Ημερομηνία:</strong></label><br>
                    <input type="date" name="date"
                           value="<?= htmlspecialchars($_POST['date'] ?? $editRow['date']) ?>" required>
                </p>

                <p>
                    <label><strong>Θέμα:</strong></label><br>
                    <input type="text" name="topic"
                           value="<?= htmlspecialchars($_POST['topic'] ?? $editRow['topic']) ?>" required style="width:100%;">
                </p>

                <p>
                    <label><strong>Κυρίως κείμενο:</strong></label><br>
                    <textarea name="body" rows="7" required style="width:100%;"><?= htmlspecialchars($_POST['body'] ?? $editRow['body']) ?></textarea>
                </p>

                <p>
                    <button type="submit" name="do_edit">Αποθήκευση</button>
                    <a href="announcements.php" style="margin-left:10px;">Ακύρωση</a>
                </p>
            </form>

            <hr>
        <?php endif; ?>

        <!-- LIST (always visible)-->
		
        <?php if (!$rows): ?>
            <p>Δεν υπάρχουν ανακοινώσεις.</p>
        <?php endif; ?>

        <?php foreach ($rows as $r): ?>
            <h2>Ανακοίνωση #<?= (int)$r['id'] ?></h2>

            <p><strong>Ημερομηνία:</strong> <?= htmlspecialchars($r['date']) ?></p>
            <p><strong>Θέμα:</strong> <?= htmlspecialchars($r['topic']) ?></p>
            <p><?= nl2br(htmlspecialchars($r['body'])) ?></p>

            <?php if ($isTutor): ?>
                <p>
                    <a href="announcements.php?action=edit&id=<?= (int)$r['id'] ?>">Επεξεργασία</a> |
                    <a href="announcements.php?delete=<?= (int)$r['id'] ?>"
                       onclick="return confirm('Σίγουρα διαγραφή;')">Διαγραφή</a>
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
