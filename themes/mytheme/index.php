<?php get_header(); ?>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <h2><?php the_title(); ?></h2>
                <div><?php the_content(); ?></div>
            <?php endwhile;
            else : ?>
                <p><?php esc_html_e('Sorry, no posts matched your criteria.', 'mytheme'); ?></p>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <?php get_sidebar(); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>

