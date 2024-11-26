<div id="comments" class="comments-area">

    <?php
    // Получаем глобальную переменную $comments
    global $comments;

    // Проверяем, есть ли комментарии
    if (have_comments()) : ?>
        <h2 class="comments-title">
            <?php
            printf(
                _nx('Один комментарий', '%1$s комментариев', get_comments_number(), 'comments title'),
                number_format_i18n(get_comments_number())
            );
            ?>
        </h2>

        <ol class="comment-list">
            <?php
            // Выводим список комментариев
            wp_list_comments(array(
                'style' => 'ol',
                'short_ping' => true,
            ));
            ?>
        </ol>

        <?php
        // Навигация по комментариям, если их много
        the_comments_navigation();

    else : // Если комментариев нет
        ?>
        <p class="no-comments"><?php esc_html_e('Пока комментариев нет.', 'text-domain'); ?></p>
    <?php endif; ?>

    <?php
    $args = array(
        'title_reply' => ('Оставьте комментарий'), // Заголовок формы
        'label_submit' => ('Отправить'), // Текст кнопки отправки
    );
    comment_form($args);
    ?>
</div>
