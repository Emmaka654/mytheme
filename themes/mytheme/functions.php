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
    register_post_type('order',
        array(
            'labels' => array(
                'name' => __('Orders'),
                'singular_name' => __('Order')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'orders'),
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
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

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
            'post_type' => 'order',
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