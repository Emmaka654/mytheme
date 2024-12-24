<?php
function mytheme_setup()
{
    // Подключение поддержки миниатюр
    add_theme_support('post-thumbnails');

    // Регистрация меню
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'mytheme'),
    ));
}

add_action('after_setup_theme', 'mytheme_setup');

function my_theme_enqueue_styles()
{
    // Подключение стилей
    wp_enqueue_style('my-style', get_stylesheet_directory_uri() . './style.css');
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
}

add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles', 10);

function register_post_types()
{
    register_post_type('product',
        array(
            'labels' => array(
                'name' => __('Products'),
                'singular_name' => __('Product')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'products'),
            'supports' => array('title', 'editor', 'thumbnail', 'comments'),
            'menu_icon' => 'dashicons-cart', // Иконка для меню
        )
    );
}

add_action('init', 'register_post_types');

function register_order_post_type()
{
    register_post_type('custom_order',
        array(
            'labels' => array(
                'name' => __('Orders'),
                'singular_name' => __('Order')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'custom_orders'),
            'supports' => array('title', 'editor', 'thumbnail', 'comments'),
            'menu_icon' => 'dashicons-list-view', // Иконка для меню
        )
    );
}

add_action('init', 'register_order_post_type');

function enqueue_custom_scripts()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('cart-script', get_template_directory_uri() . '/cart.js', array('jquery'), null, true);

    // Передаем переменную ajax_object в скрипт
    wp_localize_script('cart-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp_rest')
    ));
}

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

// Создаем сайдбар
function my_custom_sidebar()
{
    register_sidebar(array(
        'name' => 'Сайдбар архива товаров',
        'id' => 'archive_products_sidebar',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));
}

add_action('init', 'my_custom_sidebar'); // Событие, которое срабатывает, когда инициализируются виджеты

require_once get_template_directory() . '/product-rating-widget.php';

// Добавляем товар в корзину
add_action('wp_ajax_add_to_cart', 'add_to_cart');
add_action('wp_ajax_nopriv_add_to_cart', 'add_to_cart');

function add_to_cart()
{
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Открываем (или создаем) массив корзины в куки
    $cart = isset($_COOKIE['cart']) ? json_decode(stripslashes($_COOKIE['cart']), true) : array();

    // Добавляем товар в корзину
    if (isset($cart[$product_id])) {
        $cart[$product_id] += $quantity; // увеличиваем количество
    } else {
        $cart[$product_id] = $quantity; // добавляем новый товар
    }

    // Сохраняем обновленный массив корзины в куки
    setcookie('cart', json_encode($cart), time() + (86400 * 30), '/'); // 30 дней

    $response = [
        'success' => true,
        'data' => [
            'product_id' => $product_id,
            'quantity' => $quantity
        ]
    ];

    echo json_encode($response);
    exit();
}

add_action('wp_ajax_get_cart_items', 'get_cart_items');
add_action('wp_ajax_nopriv_get_cart_items', 'get_cart_items');

function get_cart_items()
{
    if (!isset($_POST['ids'])) {
        wp_send_json_error('No product IDs provided.');
        return;
    }

    $ids = explode(',', sanitize_text_field($_POST['ids']));
    // Получаем строку с количеством товаров
    $quantities_json = isset($_POST['quantities']) ? sanitize_text_field($_POST['quantities']) : '';
    // Удаляем лишние обратные слеши
    $quantities_json = stripslashes($quantities_json);
    // Декодируем JSON-строку в ассоциативный массив
    $quantities = json_decode($quantities_json, true);

    $items = [];

    foreach ($ids as $id) {
        $product = get_post($id); // Получаем пост по ID
        // Получаем цену
        $price = get_post_meta($id, 'price', true);
        $items[] = [
            'id' => $id,
            'title' => $product->post_title,
            'price' => $price,
            'quantity' => isset($quantities[$id]) ? $quantities[$id] : 0,
        ];
    }

    if (empty($items)) {
        wp_send_json_error('No items found for provided IDs.');
        return;
    }

    $response = [
        'success' => true,
        'data' => $items // массив с данными о товарах
    ];

    echo json_encode($response);
    exit;
}

function submit_order()
{
    parse_str($_POST['formData'], $form_data);
    $cart = $_POST['cart'];

    // Получаем данные из формы
    $fio_user = sanitize_text_field($form_data['fio_user']);
    $email_user = sanitize_email($form_data['email_user']);
    $products = array_keys($cart);
    // Проверяем, что данные были переданы
    if (!empty($fio_user) && !empty($email_user) && !empty($products)) {
        // Создаем новый заказ (пост типа ORDER)
        $order_id = wp_insert_post(array(
            'post_title' => 'Заказ от ' . $fio_user,
            'post_type' => 'custom_order',
            'post_status' => 'publish',
        ));

        // Сохраняем мета поля
        if ($order_id) {
            update_field('fio_user', $fio_user, $order_id);
            update_field('email_user', $email_user, $order_id);
            update_field('products', $products, $order_id);

            $response = [
                'success' => true,
                'message' => 'Заказ успешно оформлен!'
            ];
            echo json_encode($response);
        } else {
            wp_send_json_error('Failed to create order. Please try again.');
            return;
        }
    } else {
        wp_send_json_error('Incorrect data.');
        return;
    }

    exit();
}

add_action('wp_ajax_submit_order', 'submit_order');
add_action('wp_ajax_nopriv_submit_order', 'submit_order');

function get_user_email()
{
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Пользователь не авторизован'));
    }

    $user = wp_get_current_user();
    $email = $user->user_email;
    wp_send_json_success(array('email' => $email));
}

add_action('wp_ajax_get_user_email', 'get_user_email');
add_action('wp_ajax_nopriv_get_user_email', 'get_user_email');

function add_account_menu_item($items, $args)
{
    if (is_user_logged_in()) {
        $items .= '<li><a href="' . home_url('/личный-кабинет/') . '">Личный кабинет</a></li>';
    }
    return $items;
}

add_filter('wp_nav_menu_items', 'add_account_menu_item', 20, 2);

// создание маршрута
add_action('rest_api_init', function () {

    // пространство имен
    // Пространство имени должно состоять из двух частей: vendor/package, где vendor - это поставщик, а package - это версия кода указанного поставщика.
    $namespace = 'custom/v1';

    // маршрут
    $route = '/orders/';

    // параметры конечной точки (маршрута)
    $route_params = [
        'methods' => 'GET',
        //callback — функция обратного вызова для ответа на запрос
        'callback' => 'get_user_orders',

        // permission_callback — функцию обратного вызова для проверки права доступа к конечной точке.
        'permission_callback' => function ($request) {
            // только авторизованный юзер имеет доступ к эндпоинту
            return is_user_logged_in();
        },
    ];

    register_rest_route($namespace, $route, $route_params);
});

function get_user_orders(WP_REST_Request $request)
{
    if (!is_user_logged_in()) {
        return new WP_Error('not_logged_in', 'Пользователь не авторизован', array('status' => 401));
    }

    $user = wp_get_current_user();
    $email = $user->user_email;
    // Проверяем email из запроса
    if ($request->get_header('email') !== $email) {
        return new WP_Error('invalid_email', 'Email не соответствует текущему пользователю', array('status' => 402));
    }

    $args = array(
        'post_type' => 'custom_order',
        'meta_query' => array(
            array(
                'key' => 'email_user', // Ключ метаполя
                'value' => $email,
                'compare' => '='
            )
        )
    );

    $orders = get_posts($args);
    $response_data = array(); // Массив для хранения данных о заказах

    // Проверяем, есть ли заказы
    if (!empty($orders)) {
        foreach ($orders as $order) {
            $order_id = $order->ID; // ID заказа
            $order_date = get_the_date('Y-m-d H:i:s', $order_id);
            $products = get_post_meta($order_id, 'products', true);
            $status = get_post_meta($order_id, 'order_status', true);
            $response_data[] = array(
                'order_id' => $order_id,
                'order_date' => $order_date,
                'products' => $products,
                'status' => $status);
        }
        return new WP_REST_Response($response_data, 200); // Возвращаем массив заказов
    } else {
        return new WP_REST_Response(array('message' => 'Заказ с таким email не найден.'), 404); // Если заказы не найдены
    }
}
