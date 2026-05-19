<?php
require_once "config.php";
require_once "auth.php";
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Αρχική Σελίδα</title>
    <link rel="stylesheet" href="style.css">
</head>

<body id="top">

<div id="container">

    <div id="header">
        <h1>Αρχική Σελίδα</h1>
    </div>

    <div id="menu">
        <a href="index.php">
            <img src="images/home.jpg" alt="Αρχική">
            <span>Αρχική</span>
        </a>

        <a href="announcements.php">
            <img src="images/announcements.jpg" alt="Ανακοινώσεις">
            <span>Ανακοινώσεις</span>
        </a>

        <a href="communication.php">
            <img src="images/communication.jpg" alt="Επικοινωνία">
            <span>Επικοινωνία</span>
        </a>

        <a href="documents.php">
            <img src="images/documents.jpg" alt="Έγγραφα">
            <span>Έγγραφα</span>
        </a>

        <a href="homework.php">
            <img src="images/homework.jpg" alt="Εργασίες">
            <span>Εργασίες</span>
        </a>

        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'Tutor'): ?>
            <a href="users.php"><img src="images/users.jpg" alt="Χρήστες"><span>Χρήστες</span></a>
        <?php endif; ?>
		
		<a href="logout.php" style="margin-top:10px;">
		<img src="images/exit.jpg">
            <span>Έξοδος</span>
        </a>
		
		 
		
    </div>

<div id="content">

    <p style="text-align:right; color:#333;">
        Συνδεδεμένος/η:
        <strong><?= htmlspecialchars($_SESSION['user']['first_name'] . " " . $_SESSION['user']['last_name']) ?></strong>
        (<?= htmlspecialchars($_SESSION['user']['role']) ?>)
    </p>

    <p>
        Καλωσορίσατε στον ιστοχώρο του μαθήματος «Εκπαιδευτικά Περιβάλλοντα Διαδικτύου».
        Ο παρών ιστοχώρος δημιουργήθηκε με στόχο την υποστήριξη των φοιτητών του μαθήματος
        και την παροχή οργανωμένης πληροφόρησης σχετικά με το περιεχόμενο και τις δραστηριότητές του.
    </p>

    <p>
        Στην ενότητα <strong>Ανακοινώσεις</strong> οι φοιτητές μπορούν να ενημερώνονται για
        σημαντικές πληροφορίες και ανακοινώσεις που αφορούν το μάθημα.
        Στην ενότητα <strong>Επικοινωνία</strong> παρέχεται η δυνατότητα αποστολής e-mail
        στον διδάσκοντα, είτε μέσω φόρμας είτε μέσω ηλεκτρονικής διεύθυνσης.
    </p>

    <p>
        Στην ενότητα <strong>Έγγραφα Μαθήματος</strong> είναι διαθέσιμο εκπαιδευτικό υλικό
        και σημειώσεις προς λήψη, ενώ στην ενότητα <strong>Εργασίες</strong> παρουσιάζονται
        οι εκφωνήσεις των εργασιών, οι στόχοι τους και οι ημερομηνίες παράδοσης.
    </p>

    <img src="images/auth.png" alt="Εκπαιδευτικό περιβάλλον" class="home-image">

</div>


</div>

</body>
</html>
