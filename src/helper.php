<?php

defined('BASEPATH') or exit('No direct script access allowed');

use app\services\messages\Message;
use app\services\messages\PopupMessage;

function app_admin_head()
{
    hooks()->do_action('app_admin_head');
}

/**
 * @return null
 * @since 2.3.2
 */
function app_admin_footer()
{
    /**
     * @deprecated 2.3.2 Use app_admin_footer instead
     */
    do_action_deprecated('after_js_scripts_render', [], '2.3.2', 'app_admin_footer');

    hooks()->do_action('app_admin_footer');
}

/**
 * @param boolean $aside should include aside
 * @since  1.0.0
 * Init admin head
 */
function init_head($aside = TRUE)
{
    $CI = &get_instance();
    $CI->load->view('admin/includes/head');
    $CI->load->view('admin/includes/header', ['startedTimers' => $CI->misc_model->get_staff_started_timers()]);
    $CI->load->view('admin/includes/setup_menu');
    if ($aside == TRUE)
    {
        $CI->load->view('admin/includes/aside');
    }
}

/**
 * @since  1.0.0
 * Init admin footer/tails
 */
function init_tail()
{
    $CI = &get_instance();
    $CI->load->view('admin/includes/scripts');

    // load custom tail script
    echo $CI->appened_tail_script ?? '';
}


