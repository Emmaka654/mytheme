<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand mx-auto" href="<?php echo home_url(); ?>"><?php echo get_bloginfo('name'); ?></a>
        <a href="<?php echo home_url('/products/'); ?>">Catalog</a>
        <a href="#" id="cart-icon">
            <img src="<?php echo home_url('/wp-content/uploads/2024/11/cart-1.png'); ?>" alt="Корзина"
                 style="width: 50px; height: auto;">
            <span id="cart-count">0</span>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <?php
        if (function_exists('custom_auth_buttons')) {
            echo custom_auth_buttons(); // Вызов функции для отображения кнопок
        } else {
            echo 'Функция отображения кнопок не найдена';
        }
        ?>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'class' => 'navbar-nav',
                'container' => false,
                'items_wrap' => '<ul class="%2$s">%3$s</ul>',
                'fallback_cb' => false,
            ));
            ?>
        </div>
    </div>
</nav>