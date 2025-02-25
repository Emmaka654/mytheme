<footer class="footer">
    <div class="container">
        <p class="text-center" style="white-space: pre;">&copy; <?php echo date('Y'); ?> Все права защищены.</p>
        <p class="text-center"><a href="<?php echo esc_url(home_url('/privacy-policy')); ?>">Политика
                конфиденциальности</a></p>
    </div>
</footer>

<?php wp_footer(); ?>
</body>

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

<div id="registration-container" style="display: none;"><?php
    if (function_exists('custom_registration_function')) {
        custom_registration_function();
    } else {
        echo "Функция регистрации не найдена";
    } ?>
</div>
<div id="authorization-container" style="display: none;"><?php
    if (function_exists('custom_authorization_function')) {
        custom_authorization_function();
    } else {
        echo "Функция авторизации не найдена";
    } ?>
</div>
</html>