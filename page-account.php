<?php
/**
 * Template Name: Личный кабинет
 */

get_header(); ?>

<div class="account-page">
    <!--определяем id текущей страницы-->
    <?php $page_id = get_the_ID();
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
        <div id="pagination"></div>
    <?php else: ?>
        <p>Пожалуйста, войдите, чтобы увидеть вашу информацию.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>

<script>
    // Выносим функцию loadOrders в глобальную область видимости
    function loadOrders(page) {
        const userEmail = '<?php echo esc_js(wp_get_current_user()->user_email); ?>';
        const perPage = 8; // Количество заказов на странице

        // Передаем параметры запроса
        fetch(`<?php echo esc_url(rest_url('custom/v1/orders')); ?>?page=${page}&per_page=${perPage}`, {
            method: 'GET',
            credentials: 'include', // Включаем куки для авторизации
            headers: {
                'X-WP-Nonce': ajax_object.nonce, // Добавляем nonce в заголовок
                'email': userEmail // Добавляем почту пользователя в заголовок
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сети: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                const ordersDiv = document.getElementById('user-orders');
                const paginationDiv = document.getElementById('pagination');

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

                    // Отображаем кнопки пагинации
                    let paginationHtml = '';
                    if (page > 1) {
                        paginationHtml += `<button onclick="loadOrders(${page - 1})">Предыдущая</button>`;
                    }
                    if (data.length === perPage) {
                        paginationHtml += `<button onclick="loadOrders(${page + 1})">Следующая</button>`;
                    }
                    paginationDiv.innerHTML = paginationHtml;
                } else {
                    ordersDiv.innerHTML = '<p>Произошла ошибка при получении заказов.</p>';
                }
            })
            .catch(error => console.error('Ошибка:', error));
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Загружаем первую страницу при загрузке страницы
        loadOrders(1);
    });
</script>
