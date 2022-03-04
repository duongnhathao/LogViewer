<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><h1><i class="fa-solid fa-swatchbook"></i> LogViewer</h1>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                    aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav">
                    <a class="nav-link" aria-current="page" href="<?php if ( ! empty($CILogViewer))
                    {
                        echo $CILogViewer->get_url() . '/dashboard';
                    } ?>">Dashboard</a>
                    <a class="nav-link" href="<?php if ( ! empty($CILogViewer))
                    {
                        echo $CILogViewer->get_url() . '/logs';
                    } ?>">Logs</a>
                </div>
            </div>
        </div>
    </nav>
</div>
