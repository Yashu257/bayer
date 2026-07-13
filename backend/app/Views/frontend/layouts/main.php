<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO -->
    <title><?= \Core\Security\Sanitizer::e($metaTitle ?? 'PharmaWebcast') ?></title>
    <meta name="description" content="<?= \Core\Security\Sanitizer::e($metaDesc ?? '') ?>">
    <meta name="robots" content="index, follow">

    <!-- Open Graph -->
    <?php if (!empty($event)): ?>
    <meta property="og:title"       content="<?= \Core\Security\Sanitizer::e($event->title) ?>">
    <meta property="og:description" content="<?= \Core\Security\Sanitizer::e($event->short_description ?? '') ?>">
    <meta property="og:type"        content="website">
    <?php endif; ?>

    <!-- Bootstrap 5 CSS -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- Global styles -->
    <link rel="stylesheet" href="/assets/css/app.css">

    <!-- Page-specific styles -->
    <?php if (!empty($pageStyles)): ?>
        <?php foreach ($pageStyles as $style): ?>
        <link rel="stylesheet" href="<?= \Core\Security\Sanitizer::e($style) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?= \Core\Security\Sanitizer::e($bodyClass ?? '') ?>">

    <?php include APP_PATH . '/Views/frontend/partials/header.php'; ?>

    <main id="main-content">
        <?php echo $content; ?>
    </main>

    <?php include APP_PATH . '/Views/frontend/partials/footer.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmLXleGMPAGnMUqH4JGekAXHFk6"
        crossorigin="anonymous"
        defer
    ></script>

    <!-- Global scripts -->
    <script src="/assets/js/app.js" defer></script>

    <!-- Page-specific scripts -->
    <?php if (!empty($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
        <script src="<?= \Core\Security\Sanitizer::e($script) ?>" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Inline scripts (e.g. countdown seed data) -->
    <?php if (!empty($inlineScript)): ?>
    <script>
        <?= $inlineScript ?>
    </script>
    <?php endif; ?>

</body>
</html>
