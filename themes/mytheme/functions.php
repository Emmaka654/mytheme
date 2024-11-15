<?php
function mytheme_setup() {
    // Подключение поддержки миниатюр
    add_theme_support('post-thumbnails');

    // Регистрация меню
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'mytheme'),
    ));
}

add_action('after_setup_theme', 'mytheme_setup');

function mytheme_scripts() {
    // Подключение стилей
    wp_enqueue_style('style', get_stylesheet_uri());
}

add_action('wp_enqueue_scripts', 'mytheme_scripts');

