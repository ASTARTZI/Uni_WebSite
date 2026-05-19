<?php
require_once "config.php";
require_once "auth.php";

$isTutor = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'Tutor';
$action = $_GET['action'] ?? '';  // '', 'add', 'edit'
$errors = [];

/* DELETE (Tutor only)*/

if ($isTutor && isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM homework WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: homework.php");
    exit;
}

/* ADD/EDIT POST (Tutor only)*/

if ($isTutor && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $goals        = trim($_POST['goals'] ?? '');
    $deliverables = trim($_POST['deliverables'] ?? '');
    $due_date     = trim($_POST['due_date'] ?? '');
    $file_path    = trim($_POST['file_path'] ?? '');
    $id           = (int)($_POST['id'] ?? 0);

    if ($goals === '')        $errors[] = "Συμπλήρωσε στόχους.";
    if ($deliverables === '') $errors[] = "Συμπλήρωσε παραδοτέα.";
    if ($due_date === '')     $errors[] = "Συμπλήρωσε ημερομηνία παράδοσης.";
    if ($file_path === '')    $errors[] = "Συμπλήρωσε εκφώνηση (όνομα/θέση αρχείου).";

    if (!$errors) {

        // ADD
        if (isset($_POST['do_add'])) {
            $pdo->beginTransaction();
            try {
                // 1) Insert homework
                $stmt = $pdo->prepare("
                    INSERT INTO homework (goals, deliverables, file_path, due_date)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$goals, $deliverables, $file_path, $due_date]);

                // 2) Get new assignment id
                $newId = (int)$pdo->lastInsertId();

                // 3) Auto insert announcement
                $today = date('Y-m-d');
                $topic = "Υποβλήθηκε η εργασία " . $newId;
                $body  = "Η ημερομηνία παράδοσης της εργασίας είναι " . $due_date;

                $stmt2 = $pdo->prepare("
                    INSERT INTO announcements (`date`, topic, body)
                    VALUES (?, ?, ?)
                ");
                $stmt2->execute([$today, $topic, $body]);

                $pdo->commit();
                header("Location: homework.php");
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Σφάλμα αποθήκευσης εργασίας/ανακοίνωσης.";
                $action = 'add';
            }
        }

        // EDIT
        if (isset($_POST['do_edit']) && $id > 0) {
            $stmt = $pdo->prepare("
                UPDATE homework
                SET goals = ?, deliverables = ?, file_path = ?, due_date = ?
                WHERE id = ?
            ");
            $stmt->execute([$goals, $deliverables, $file_path, $due_date, $id]);

            header("Location: homework.php");
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
        $stmt = $pdo->prepare("SELECT * FROM homework WHERE id = ?");
        $stmt->execute([$id]);
        $editRow = $stmt->fetch();
        if (!$editRow) {
            header("Location: homework.php");
            exit;
        }
    } else {
        header("Location: homework.php");
        exit;
    }
}

/* LIST */

$rows = $pdo->query("SELECT * FROM homework ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Εργασίες</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="top">

<div id="container">

    <div id="header">
        <h1>Εργασίες</h1>
    </div>

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
                <a href="homework.php?action=add"
                   style="display:inline-block; padding:8px 12px; border:1px solid #999; border-radius:4px; background:#f3f3f3; text-decoration:none;">
                    Προσθήκη νέας εργασίας
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
        <?php if ($isTutor && $action === 'add'): ?>
            <h2>Προσθήκη νέας εργασίας</h2>

            <form method="post" action="homework.php?action=add">
                <p><strong>Στόχοι:</strong><br>
                    <textarea name="goals" rows="6" required style="width:100%;"><?= htmlspecialchars($_POST['goals'] ?? '') ?></textarea>
                </p>

                <p><strong>Παραδοτέα:</strong><br>
                    <textarea name="deliverables" rows="5" required style="width:100%;"><?= htmlspecialchars($_POST['deliverables'] ?? '') ?></textarea>
                </p>

                <p><strong>Εκφώνηση (όνομα/θέση αρχείου):</strong><br>
                    <input type="text" name="file_path" value="<?= htmlspecialchars($_POST['file_path'] ?? '') ?>" required style="width:100%;">
                </p>

                <p><strong>Ημερομηνία παράδοσης:</strong><br>
                    <input type="date" name="due_date" value="<?= htmlspecialchars($_POST['due_date'] ?? '') ?>" required>
                </p>

                <p>
                    <button type="submit" name="do_add">Αποθήκευση</button>
                    <a href="homework.php" style="margin-left:10px;">Ακύρωση</a>
                </p>
            </form>
            <hr>
        <?php endif; ?>

        <!-- EDIT FORM -->
        <?php if ($isTutor && $action === 'edit' && $editRow): ?>
            <h2>Επεξεργασία εργασίας #<?= (int)$editRow['id'] ?></h2>

            <form method="post" action="homework.php?action=edit&id=<?= (int)$editRow['id'] ?>">
                <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">

                <p><strong>Στόχοι:</strong><br>
                    <textarea name="goals" rows="6" required style="width:100%;"><?= htmlspecialchars($_POST['goals'] ?? $editRow['goals']) ?></textarea>
                </p>

                <p><strong>Παραδοτέα:</strong><br>
                    <textarea name="deliverables" rows="5" required style="width:100%;"><?= htmlspecialchars($_POST['deliverables'] ?? $editRow['deliverables']) ?></textarea>
                </p>

                <p><strong>Εκφώνηση (όνομα/θέση αρχείου):</strong><br>
                    <input type="text" name="file_path" value="<?= htmlspecialchars($_POST['file_path'] ?? $editRow['file_path']) ?>" required style="width:100%;">
                </p>

                <p><strong>Ημερομηνία παράδοσης:</strong><br>
                    <input type="date" name="due_date" value="<?= htmlspecialchars($_POST['due_date'] ?? $editRow['due_date']) ?>" required>
                </p>

                <p>
                    <button type="submit" name="do_edit">Αποθήκευση</button>
                    <a href="homework.php" style="margin-left:10px;">Ακύρωση</a>
                </p>
            </form>
            <hr>
        <?php endif; ?>

        <!-- LIST -->
        <?php if (!$rows): ?>
            <p>Δεν υπάρχουν εργασίες.</p>
        <?php endif; ?>

        <?php $n = 1; foreach ($rows as $r): ?>
            <h2>Εργασία <?= $n ?></h2>

            <p><strong>Στόχοι:</strong></p>
            <ol>
                <?php
                $goalsList = preg_split("/\r\n|\n|\r/", $r['goals']);
                foreach ($goalsList as $g):
                    $g = trim($g);
                    if ($g !== ''):
                ?>
                    <li><?= htmlspecialchars($g) ?></li>
                <?php endif; endforeach; ?>
            </ol>

            <p><strong>Εκφώνηση:</strong>
                <a href="<?= htmlspecialchars($r['file_path']) ?>">Κατεβάστε την εκφώνηση</a>
            </p>

            <p><strong>Παραδοτέα:</strong></p>
            <ul>
                <?php
                $delList = preg_split("/\r\n|\n|\r/", $r['deliverables']);
                foreach ($delList as $d):
                    $d = trim($d);
                    if ($d !== ''):
                ?>
                    <li><?= htmlspecialchars($d) ?></li>
                <?php endif; endforeach; ?>
            </ul>

            <p><strong>Ημερομηνία παράδοσης:</strong> <?= htmlspecialchars($r['due_date']) ?></p>

            <?php if ($isTutor): ?>
                <p>
                    <a href="homework.php?action=edit&id=<?= (int)$r['id'] ?>">Επεξεργασία</a> |
                    <a href="homework.php?delete=<?= (int)$r['id'] ?>" onclick="return confirm('Σίγουρα διαγραφή;')">Διαγραφή</a>
                </p>
            <?php endif; ?>

            <hr>
        <?php $n++; endforeach; ?>

        <p style="text-align:right;">
            <a href="#top">top</a>
        </p>

    </div>
</div>

</body>
</html>
