<?php
get_header(); // Подключаем заголовок темы

if (have_posts()) : while (have_posts()) : the_post(); ?>

    <?php $product_id = get_the_ID();?>
    <div class="product-container" data-product-id="<?php echo esc_attr($product_id); ?>">
        <!-- Главная картинка товара -->
        <div class="product-image">
            <?php the_post_thumbnail('full'); // Выводим изображение товара ?>
        </div>

        <p></p>
        <!-- Заголовок товара -->
        <h1 class="product-title"><?php the_title(); ?></h1>

        <!-- Описание товара -->
        <div class="product-description">
            <?php the_content(); // Выводим описание товара ?>
        </div>

        <!-- Характеристики товара -->
        <div class="product-specifications">
            <h2>Характеристики товара</h2>
            <ul>
                <li>Цена: <?php echo get_field('price'); ?></li>
                <li>Размер: <?php
                    $sizes = get_field('size'); // Получаем массив выбранных размеров
                    echo implode(', ', $sizes)
                    ?></li>
                <li>Цвет: <?php echo get_field('color'); ?></li>
                <li>Рейтинг: <?php echo get_field('product_rating'); ?></li>
            </ul>
        </div>

        <!-- Кнопка "В корзину" -->
        <?php $product_id = get_the_ID();?>
        <button class="add-to-cart-button" data-product-id="<?php echo esc_attr($product_id); ?>">В корзину</button>

        <!-- Комментарии и форма комментариев -->
        <div class="comments-section">
            <?php
            comments_template();
            ?>
        </div>
    </div>

<?php endwhile;
else : ?>
    <p><?php esc_html_e('Извините, товар не найден.'); ?></p>
<?php endif; ?>

<?php
get_footer(); // Подключаем футер темы
