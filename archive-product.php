<?php
get_header(); // Подключаем шапку темы

// Устанавливаем параметры запроса
$pageCurrent = isset($_GET['page']) ? intval($_GET['page']) : 1; // Получаем текущую страницу
$args = array(
    'post_type' => 'product',
    'posts_per_page' => 8, // Количество товаров на странице
    'paged' => $pageCurrent, // Текущая страница
);

// Запрос
$query = new WP_Query($args);

if ($query->have_posts()) : ?>
    <div class="product-archive">
        <div class="container">
            <div class="row">
                <?php
                // Цикл для вывода товаров
                while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="col-md-3"> <!-- Для Bootstrap 4: 4 товара в ряд -->
                        <div class="product-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium'); // Изображение товара ?>
                                </a>
                            <?php endif; ?>
                            <h2><?php the_title(); // Название товара ?></h2>
                            <p class="price"><?php echo get_post_meta(get_the_ID(), 'price', true); // Цена товара ?></p>
                            <p><?php the_excerpt(); // Короткое описание товара ?></p>
                            <a href="<?php the_permalink(); ?>" class="btn btn-primary">Купить</a>
                            <!-- Кнопка "Купить" -->
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="pagination">
            <?php
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $pageCurrent,
                'format' => '?page=%#%',
                'prev_text' => '<< Назад ',
                'next_text' => ' Вперед >>',
            ));
            ?>
        </div>

    </div>
<?php else : ?>
    <p><?php _e('No products.'); ?></p>
<?php endif; ?>

<aside class="sidebar">
    <?php
    // Выводим сайдбар
    if (function_exists('dynamic_sidebar') && is_active_sidebar('archive_products_sidebar')) {
        dynamic_sidebar('archive_products_sidebar');
    }
    ?>
</aside>

<?php
// Сбрасываем глобальную переменную поста
wp_reset_postdata();

get_footer(); // Подключаем футер темы
?>
