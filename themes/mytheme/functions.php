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

function register_post_types(){
    register_post_type('product',
        array(
            'labels' => array(
                'name' => __('Products'),
                'singular_name' => __('Product')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'products'),
            'supports' => array('title', 'editor', 'thumbnail'),
            'menu_icon' => 'dashicons-cart', // Иконка для меню
        )
    );
}

add_action('init', 'register_post_types');

function my_theme_enqueue_styles() {
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
}

add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');
