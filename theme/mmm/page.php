<?php
/**
 * Generic page fallback.
 *
 * Every designed route has its own template; this one catches any other WP
 * page (a future page, a draft opened by its preview link) and renders it
 * with the site chrome, the site's typography and nothing else.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="shell-wide pb-20 pt-32 sm:pt-40">
<div class="mx-auto max-w-3xl">
<?php
while ( have_posts() ) :
	the_post();
	?>
<article <?php post_class(); ?>>
<h1 class="font-display text-2xl font-black text-[#0A1020] sm:text-3xl"><?php the_title(); ?></h1>
<div class="rich mt-4 text-[0.98rem] leading-relaxed text-[#334155]"><?php the_content(); ?></div>
</article>
	<?php
endwhile;
?>
</div>
</div>
<?php
get_footer();
