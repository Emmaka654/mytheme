<?php get_header(); ?>

<div class="container">
    <h1 class="my-4"><?php the_archive_title(); ?></h1>
    <div class="row">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php the_title(); ?></h5>
                        <p class="card-text"><?php the_excerpt(); ?></p>
                        <a href="<?php the_permalink(); ?>" class="btn btn-primary">Читать далее</a>
                    </div>
                </div>
            </div>
        <?php endwhile; else : ?>
            <p><?php esc_html_e('Sorry, no posts found.', 'mytheme'); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php get_footer(); ?>
