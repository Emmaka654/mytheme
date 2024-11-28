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
        <a href="<?php echo home_url('/products/'); ?>">Каталог</a>
        <a href="#account">Личный кабинет</a>
        <a href="#" id="cart-icon">
            <img src="http://localhost/wp/wp-content/uploads/2024/11/cart-1.png" alt="Корзина"
                 style="width: 50px; height: auto;">
            <span id="cart-count">0</span>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
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

<!-- Всплывающее окно корзины -->
<div class="cart-popup" id="cart-popup">
    <div class="cart-header">
        <h2>Корзина</h2>
        <button id="close-cart-popup">&times;</button>
    </div>
    <div class="cart-items" id="cart-items"></div>
    <div class="cart-footer">
        <button id="checkout-button">Оформить заказ</button>
    </div>
</div>

<form id="order-form">
    <h3>Введите свои данные для оформления заказа</h3>
    <label for="fio_user">ФИО пользователя:</label>
    <input type="text" id="fio_user" name="fio_user" required>

    <label for="email_user">Email пользователя:</label>
    <input type="email" id="email_user" name="email_user" required>

    <button type="submit" id="submit-button">Отправить</button>
    <button type="button" id="close-form">Отмена</button>
</form>

<div id="order-response"></div>