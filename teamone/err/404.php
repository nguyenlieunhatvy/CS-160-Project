<!DOCTYPE HTML>

<?php require_once "StandardPage.php"; ?>

<html>
<head>
    <?php $page = new StandardPage("Page Not Found"); ?>
</head>

<body>
<?php $page->printHeader(); ?>
<div id="main_container">
    <?php $page->print404ErrorMessage(); ?>
</div>
<?php $page->printFooter(); ?>
</body>
</html>
