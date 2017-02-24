<?php
/**
 * Smarty plugin
 *
 */

/**
 * Smarty pagination  plugin
 */

function smarty_function_pagination($params, $template) {
    $var_name = $params['var_name'];
    $total_page = intval($params['total_page']);
    $current_page = intval($params['current_page']);
    $show_page_count = intval($params['show_page_count']);

    $pagination = array(
        'first_page' => 1,
        'last_page' => $total_page,
        'total_page' => $total_page,
        'current_page' => $current_page,
        'pages' => array(),
        'prev_page' => ($current_page > 1) ? $current_page - 1 : 1,
        'next_page' => ($current_page < $total_page) ? $current_page + 1 : $total_page,
    );

    $start_page = $current_page - 2;
    if ($current_page + 2 > $total_page) {
        $start_page -= ($current_page + 2 - $total_page);
    }
    if ($start_page < 1) {
        $start_page = 1;
    }

    $show_page_count = $show_page_count == 0 ? 5 : $show_page_count;
    $end_page = $start_page + $show_page_count;

    if ($end_page > $total_page) {
        $end_page = $total_page;
    }

    if ($start_page > 1) {
        $pagination['ellipsis_start'] = true;
    }
    if ($end_page < $total_page) {
        $pagination['ellipsis_end'] = true;
    }

    for ($i = $start_page; $i <= $end_page; $i += 1) {
        $pagination['pages'][] = $i;
    }

    $template->assign($var_name, $pagination);
    return '';
}



