<!-- header.php -->
<?php
if (isset($load_DB) && $load_DB) {
    require BaseDir::getFullPath("config/database.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1,user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title><?= isset($title) ? $title : "" ?></title><!-- Title dynamically set by the page -->

    <!-- font import  -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="<?php echo BaseDir::getProjectLink('lib/bootstrap/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Owl Carousel cdn  -->
    <?php if (isset($load_owlcarousel_cdn) && $load_owlcarousel_cdn): ?>
        <link rel="stylesheet" href="/lib/owlcarousel/owl.carousel.min.css">
        <link rel="stylesheet" href="/lib/owlcarousel/owl.theme.default.min.css">
    <?php endif; ?>
    <!-- Datatable cdn  -->
    <?php if (isset($load_datatable_cdn) && $load_datatable_cdn): ?>
        <link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.5.0/css/rowReorder.dataTables.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.dataTables.css">

    <?php endif; ?>

    <script src="<?php echo BaseDir::getProjectLink('functions/functions.js'); ?>"></script>
    <link rel="stylesheet" href="<?php echo BaseDir::getProjectLink('assets/css/style.css'); ?>">

    <head>

    </head>
</head>

<body id="<?= isset($body_id) ?? $body_id ?>"> <!-- Body ID dynamically set by the page -->


    <!-- Main Content -->
    <div id="content_body"></div>