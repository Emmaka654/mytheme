<?php

class top_rated_products_widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'top_rated_products_widget', // ID виджета
            __('Топ Рейтинговые Товары', 'textdomain'), // Название виджета
            array('description' => __('Показать 5 товаров с наивысшим рейтингом', 'textdomain')) // Описание
        );
    }

    public function widget($args, $instance)
    {
        $title = apply_filters('widget_title', $instance['title']);
        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        // Получаем 5 товаров с наивысшим рейтингом
        $query_args = array(
            'post_type' => 'product', // Тип поста - товар
            'posts_per_page' => 5, // Количество товаров
            'meta_key' => 'product_rating', // Мета поле для рейтинга
            'orderby' => 'meta_value_num', // Сортировка по мета полю
            'order' => 'DESC' // В порядке убывания
        );

        $top_rated_products = new WP_Query($query_args);

        if ($top_rated_products->have_posts()) {
            echo '<ul>';
            while ($top_rated_products->have_posts()) {
                $top_rated_products->the_post();
                $product_id = get_the_ID();
                $rating = get_post_meta($product_id, 'product_rating', true);
                $product_link = get_permalink($product_id);
                $product_title = get_the_title();

                echo '<li><a href="' . esc_url($product_link) . '">' . esc_html($product_title) . '</a> - Рейтинг: ' . esc_html($rating) . '</li>';
            }
            echo '</ul>';
        } else {
            echo 'Нет товаров с рейтингом.';
        }

        wp_reset_postdata();//сброс глобальной переменной $post к оригинальному значению после выполнения цикла
        echo $args['after_widget'];
    }

    // Метод для определения формы виджета в админке
    public function form($instance) // $instance - массив, который содержит текущие настройки виджета
    {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('New title', 'textdomain');
        }

        // Форма виджета в админке
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <?php __('Title:', 'textdomain'); ?>
            </label>
            <input
                    class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                    name="<?php echo $this->get_field_name('title'); ?>"
                    type="text"
                    value="<?php echo esc_attr($title); ?>"
            />
        </p>
        <?php
    }

    // Метод для обработки сохранения настроек
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : ''; //функция strip_tags() удаляет все HTML-теги из строки

        return $instance;
    }
}

// Регистрация виджета
function register_top_rated_products_widget()
{
    register_widget('top_rated_products_widget');
}

add_action('widgets_init', 'register_top_rated_products_widget');