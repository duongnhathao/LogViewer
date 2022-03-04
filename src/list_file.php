<?php include 'template/header.php'; ?>
    <body>
<?php include 'template/nav.php'; ?>
    <div class="container-fluid py-5">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="?is_simple=true" class="nav-link <?php if ( ! empty($is_simple) && $is_simple === TRUE)
                {
                    echo 'active';
                } ?>">Logs simple</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="?is_simple=false" class="nav-link <?php if ( ! $is_simple)
                {
                    echo 'active';
                } ?>">Another file log</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade <?php if ( ! empty($is_simple) && $is_simple === TRUE)
            {
                echo 'show active';
            } ?>" id="log_simple" role="tabpanel" aria-labelledby="log_simple-tab">
                <?php if ( ! empty($is_simple) && $is_simple === TRUE) { ?>
                    <div class="row">
                        <div class="col-sm-3 col-md-2 sidebar">
                            <div class="list-group">
                                <?php if (empty($files)): ?>
                                    <a class="list-group-item liv-active">No Log Files Found</a>
                                <?php else: ?>
                                    <?php foreach ($files as $file): ?>
                                        <a href="?f=<?= base64_encode($file); ?>"
                                           class="list-group-item <?= ($currentFile == $file) ? "active" : "" ?>">
                                            <?= $file; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-9 col-md-10 table-container ">
                            <?php if (is_null($logs)) { ?>
                                <div>
                                    <br><br>
                                    <strong>Log file > 50MB, please download it.</strong>
                                    <br><br>
                                </div>
                            <?php } elseif ( ! $is_content) { ?>
                                <table id="table-log" class="table table-hover table-striped">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Level</th>
                                        <th>Date</th>
                                        <th>Content</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if ( ! $is_content) { ?>
                                        <?php foreach ($logs as $key => $log): ?>
                                            <tr data-display="stack<?= $key; ?>">

                                                <td class="text-<?= $log['class']; ?>">
                                                    <i class="<?= $log['icon']; ?>"></i>
                                                    &nbsp;<?= $log['level']; ?>
                                                </td>
                                                <td class="date"><?= $log['date']; ?></td>
                                                <td class="text">
                                                    <?php if (array_key_exists("extra", $log)): ?>
                                                        <a class="pull-right expand btn btn-default btn-xs"
                                                           data-display="stack<?= $key; ?>">
                                                            <span class="glyphicon glyphicon-search"></span>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?= $log['content']; ?>
                                                    <?php if (array_key_exists("extra", $log)): ?>
                                                        <div class="stack" id="stack<?= $key; ?>"
                                                             style="display: none; white-space: pre-wrap;">
                                                            <?= $log['extra'] ?>
                                                        </div>
                                                    <?php endif; ?>

                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            <?php } elseif ($is_content) { ?>
                                <pre><?php
                                    $delimiter = isset($CILogViewer) ? $CILogViewer->get_delimiter() : NULL;
                                    $pieces = preg_split('/' . $delimiter . '/', $logs);
                                    if (isset($search))
                                    {
                                        foreach ($pieces as $piece)
                                        {
                                            if (str_contains($piece, $search))
                                            {
                                                if (isset($not_include))
                                                {
                                                    if ( ! str_contains($piece, $not_include))
                                                    {
                                                        echo $piece . $CILogViewer->delimiter;

                                                    }
                                                } else
                                                {
                                                    echo $piece . $CILogViewer->delimiter;
                                                }
                                            }
                                        }
                                    } else
                                    {
                                        $count = 0;
                                        foreach (array_reverse($pieces) as $piece)
                                        {
                                            if ( ! empty($not_include) && str_contains($piece, $not_include))
                                            {
                                                continue;
                                            }

                                            if ($count === 20)
                                            {
                                                break;
                                            }

                                            echo $piece . $CILogViewer->get_delimiter();
                                            $count++;

                                        }
                                    }
                                    ?></pre>
                            <?php } ?>
                            <div>
                                <?php if ($currentFile): ?>
                                    <a href="?dl=<?= base64_encode($currentFile); ?>" role="button"
                                       class="btn btn-outline-primary">
                                        <i class="fa-solid fa-download"></i>
                                        Download file
                                    </a>
                                    -
                                    <a id="delete-log" href="?del=<?= base64_encode($currentFile); ?>" role="button"
                                       class="btn btn-outline-warning"><i class="fa-solid fa-trash"></i>Delete file</a>
                                    <?php if (count($files) > 1): ?>
                                        -
                                        <a id="delete-all-log" href="?del=<?= base64_encode("all"); ?>" role="button"
                                           class="btn btn-outline-danger"><i class="fa-solid fa-trash-arrow-up"></i>
                                            Delete
                                            all files</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>

            </div>
            <div class="tab-pane fade <?php if ( ! $is_simple)
            {
                echo 'show active';
            } ?>" id="profile" role="tabpanel" aria-labelledby="another_logs-tab">
                <div class="row">
                    <div class="col-sm-3 col-md-2 sidebar">
                        <div class="list-group">
                            <?php if (empty($files)): ?>
                                <a class="list-group-item liv-active">No Log Files Found</a>
                            <?php else: ?>
                                <?php foreach ($files as $file): ?>
                                    <a href="?is_simple=false&f=<?= base64_encode($file); ?>"
                                       class="list-group-item <?= ($currentFile == $file) ? "active" : "" ?>">
                                        <?= $file; ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-sm-9 col-md-10 table-container ">
                        <?php if (is_null($logs)) { ?>
                            <div>
                                <br><br>
                                <strong>Log file > 50MB, please download it.</strong>
                                <br><br>
                            </div>
                        <?php } elseif ($is_content) { ?>

                            <pre><?php
                                $delimiter = isset($CILogViewer) ? $CILogViewer->get_delimiter() : NULL;
                                $pieces = preg_split('/' . $delimiter . '/', $logs);

                                if (empty($delimiter) || count($pieces) === 1)
                                {
                                    echo $logs;

                                } else
                                { ?>
                                    <table id="table-log" class="table table-hover table-striped">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Level</th>
                                        <th>Date</th>
                                        <th>Content</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $pieces = array_reverse($pieces);
                                        foreach ($pieces as $key => $log): ?>
                                            <tr data-display="stack<?= $key; ?>">

                                                <td class="text">
                                                </td>
                                                <td class="date"> </td>
                                                <td class="text">
                                                    <?= $log ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <?php }

                                ?></pre>
                        <?php } ?>
                        <div>
                            <?php if ($currentFile): ?>
                                <a href="?dl=<?= base64_encode($currentFile); ?>" role="button"
                                   class="btn btn-outline-primary">
                                    <i class="fa-solid fa-download"></i>
                                    Download file
                                </a>
                                -
                                <a id="delete-log" href="?del=<?= base64_encode($currentFile); ?>" role="button"
                                   class="btn btn-outline-warning"><i class="fa-solid fa-trash"></i>Delete file</a>
                                <?php if (count($files) > 1): ?>
                                    -
                                    <a id="delete-all-log" href="?del=<?= base64_encode("all"); ?>" role="button"
                                       class="btn btn-outline-danger"><i class="fa-solid fa-trash-arrow-up"></i> Delete
                                        all files</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<?php include 'template/footer.php'; ?>