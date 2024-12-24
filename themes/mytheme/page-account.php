<?php
/**
 * Template Name: Личный кабинет
 */

get_header(); ?>

<div class="account-page">
<!--определяем id текущей страницы-->
    <?php $page_id = get_queried_object_id();
    $title = get_the_title($page_id);
    echo '<h1>' . esc_html($title) . '</h1>';

    if (is_user_logged_in()): ?>
        <h2>Добро пожаловать, <?php echo esc_html(wp_get_current_user()->display_name); ?></h2>
        <?php $current_user = wp_get_current_user();

        $fname = get_user_meta($current_user->ID, 'fname', true);
        $email = $current_user->user_email;
        if (!empty($fname)) {
            echo '<p>Ваше имя: ' . esc_html($fname) . '</p>';
        } else {
            echo '<p>Ваше имя не указано.</p>';
        }

        echo '<p>Ваш email: ' . esc_html($email) . '</p>';
        ?>
        <div id="user-orders"></div>
    <?php else: ?>
        <p>Пожалуйста, войдите, чтобы увидеть вашу информацию.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Функция esc_js() используется для экранирования строки, чтобы она была безопасной для использования в JavaScript
        const userEmail = '<?php echo esc_js(wp_get_current_user()->user_email); ?>';
        // fetch: Это встроенная функция JavaScript, которая позволяет выполнять HTTP-запросы.
        fetch('<?php echo esc_url(rest_url('custom/v1/orders')); ?>', {
            method: 'GET',
            credentials: 'include', // Включаем куки для авторизации
            headers: {
                'X-WP-Nonce': ajax_object.nonce, // Добавляем nonce в заголовок
                'email': userEmail // Добавляем почту пользователя в заголовок
            }
        })
            .then(response => {
                // Проверяем, успешен ли ответ
                if (!response.ok) {
                    throw new Error('Ошибка сети: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                const ordersDiv = document.getElementById('user-orders');
                if (Array.isArray(data) && data.length === 0) {
                    ordersDiv.innerHTML = '<p>У вас нет заказов.</p>';
                } else if (Array.isArray(data) && data.length > 0) {
                    let ordersHtml = '<ul>';
                    data.forEach(order => {
                        ordersHtml += '<li>Заказ ID: ' + order.order_id + ', Дата заказа: ' + order.order_date +
                            ', Продукты: ' + order.products + ', Статус: ' + order.status + '</li>';
                    });
                    ordersHtml += '</ul>';
                    ordersDiv.innerHTML = ordersHtml;
                } else {
                    // Обработка случая, если data не является массивом
                    ordersDiv.innerHTML = '<p>Произошла ошибка при получении заказов.</p>';
                }
            })
            .catch(error => console.error('Ошибка:', error));
    });
</script>
