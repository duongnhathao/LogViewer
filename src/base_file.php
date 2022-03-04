<?php include 'template/header.php'; ?>
<body>
<?php include 'template/nav.php'; ?>
<div>
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <div class="list-group">
                <?php if (empty($files)): ?>
                    <a class="list-group-item liv-active">No Log Files Found</a>
                <?php else: ?>
                    <?php foreach ($files as $file): ?>
                        <a href="?f=<?= base64_encode($file); ?>"
                           class="list-group-item">
                            <?= $file; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<?php include 'template/footer.php.php'; ?>
