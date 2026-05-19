<?php
require_once "config.php";
require_once "auth.php";


function detectEmailColumn(PDO $pdo): string {
    try {
        $cols = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
        $names = array_map(fn($c) => $c['Field'], $cols);

        if (in_array('login', $names, true)) return 'login';
        if (in_array('loginame', $names, true)) return 'loginame';

        // fallback
        return 'login';
    } catch (Exception $e) {
        return 'login';
    }
}

$emailCol = detectEmailColumn($pdo);
$isTutor = isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'Tutor';

$successMsg = "";
$errorMsg = "";

function fetchAllTutors(PDO $pdo, string $emailCol): array {
    $sql = "SELECT id, {$emailCol} AS email FROM users WHERE role = 'Tutor' AND {$emailCol} IS NOT NULL AND TRIM({$emailCol}) <> ''";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$tutors = fetchAllTutors($pdo, $emailCol);


$tutorMailto = "tutor@csd.auth.test.gr";
if (!empty($tutors) && !empty($tutors[0]['email'])) {
    $tutorMailto = $tutors[0]['email'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sender  = trim($_POST['sender'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($sender === '' || $subject === '' || $message === '') {
        $errorMsg = "Συμπλήρωσε όλα τα πεδία.";
    } elseif (!filter_var($sender, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Μη έγκυρη διεύθυνση e-mail.";
    } elseif (empty($tutors)) {
        $errorMsg = "Δεν βρέθηκαν Tutors στη βάση για να σταλεί το μήνυμα.";
    } else {

        try {
            $pdo->beginTransaction();

            // 1) Αποθήκευση μηνύματος
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (sender_email, subject, body, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$sender, $subject, $message]);
            $messageId = (int)$pdo->lastInsertId();

            // 2) “Παράδοση” σε ΟΛΟΥΣ τους tutors (DB inbox)
            $stmtRec = $pdo->prepare("
                INSERT INTO contact_message_recipients (message_id, tutor_user_id, tutor_email, delivered_at)
                VALUES (?, ?, ?, NOW())
            ");

            $deliveredCount = 0;
            foreach ($tutors as $t) {
                $email = trim((string)$t['email']);
                if ($email === '') continue;

                $stmtRec->execute([$messageId, (int)$t['id'], $email]);
                $deliveredCount++;
            }

            $pdo->commit();

            $mailSuccessCount = 0;
            $mailFailCount = 0;

            $headers = [];
            $headers[] = "From: {$sender}";
            $headers[] = "Reply-To: {$sender}";
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/plain; charset=UTF-8";

            foreach ($tutors as $t) {
                $to = trim((string)$t['email']);
                if ($to === '') continue;

                $ok = @mail($to, $subject, $message, implode("\r\n", $headers));
                if ($ok) $mailSuccessCount++;
                else $mailFailCount++;
            }

            $totalTutors = count($tutors);

            $successMsg =
                "Το μήνυμα καταχωρήθηκε στη βάση και παραδόθηκε σε {$deliveredCount}/{$totalTutors} tutor(s).";

            $successMsg .= " Απόπειρα email: {$mailSuccessCount}/{$totalTutors} επιτυχίες.";
            if ($mailFailCount > 0) {
                $successMsg .= " (Κάποιες αποστολές απορρίφθηκαν από τον server.)";
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errorMsg = "Σφάλμα κατά την αποθήκευση/παράδοση του μηνύματος.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Επικοινωνία</title>
    <link rel="stylesheet" href="style.css">
</head>

<body id="top">

<div id="container">

    <div id="header">
        <h1>Επικοινωνία</h1>
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

        <a href="logout.php" style="margin-top:10px;">
            <img src="images/exit.jpg" alt="Έξοδος"><span>Έξοδος</span>
        </a>
    </div>

    <div id="content">

        <p style="text-align:right;">
            Συνδεδεμένος/η:
            <strong><?= htmlspecialchars($_SESSION['user']['first_name']." ".$_SESSION['user']['last_name']) ?></strong>
            (<?= htmlspecialchars($_SESSION['user']['role']) ?>)
        </p>

        <?php if ($successMsg): ?>
            <p style="background:#dff0d8; padding:10px; border-radius:6px;">
                <?= htmlspecialchars($successMsg) ?>
            </p>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <p style="background:#f2dede; padding:10px; border-radius:6px;">
                <?= htmlspecialchars($errorMsg) ?>
            </p>
        <?php endif; ?>

        <h2>Αποστολή e-mail μέσω web φόρμας</h2>

        <form method="post" action="communication.php">
            <p>
                <label><strong>Αποστολέας:</strong></label><br>
                <input type="email" name="sender" required placeholder="example@email.com">
            </p>

            <p>
                <label><strong>Θέμα:</strong></label><br>
                <input type="text" name="subject" required placeholder="Γράψτε το θέμα...">
            </p>

            <p>
                <label><strong>Κείμενο:</strong></label><br>
                <textarea name="message" rows="6" required placeholder="Γράψτε το μήνυμα..."></textarea>
            </p>

            <button type="submit">Αποστολή</button>
            <button type="reset">Καθαρισμός</button>
        </form>

        <h2>Αποστολή e-mail με χρήση e-mail διεύθυνσης</h2>

<p>Εναλλακτικά μπορείτε να αποστείλετε e-mail σε κάποιον/κάποιους από τους Tutors:</p>

<?php if (empty($tutors)): ?>
    <p><em>Δεν βρέθηκαν Tutors στη βάση.</em></p>
<?php else: ?>
    <ul>
        <?php foreach ($tutors as $t): ?>
            <?php $em = trim((string)$t['email']); ?>
            <?php if ($em === '') continue; ?>
            <li>
                <a href="mailto:<?= htmlspecialchars($em) ?>"><?= htmlspecialchars($em) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <p style="margin-top:10px;">
        <strong>Μαζικό e-mail:</strong>
        <a href="mailto:<?= htmlspecialchars(implode(',', array_map(fn($x) => trim((string)$x['email']), $tutors))) ?>">
            Αποστολή σε όλους (CC)
        </a>
    </p>
<?php endif; ?>


        <p style="margin-top:12px; font-size:0.95em; color:#333;">
            <strong>Σημείωση:</strong> Η “παράδοση” γίνεται σε <strong>όλους</strong> τους tutors στη βάση (5/5), ακόμη κι αν ο mail server απορρίψει κάποια πραγματικά e-mail.
        </p>

    </div>

</div>
</body>
</html>
