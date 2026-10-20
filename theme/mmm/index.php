<?php
/**
 * Fallback template.
 *
 * Every route on this site has a dedicated template; this file only handles
 * anything that falls through (search results, archives, an unexpected post
 * type) so the site still renders with the correct chrome.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="shell-wide pb-20 pt-32 sm:pt-40">
<?php if ( have_posts() ) : ?>
<div class="mx-auto max-w-3xl">
<?php
while ( have_posts() ) :
	the_post();
	?>
<article <?php post_class( 'mb-10 border-b border-[#DCE2EA] pb-8' ); ?>>
	<h1 class="font-display text-2xl font-black text-[#0A1020] sm:text-3xl"><?php the_title(); ?></h1>
	<div class="rich mt-4 text-[0.98rem] leading-relaxed text-[#334155]"><?php the_content(); ?></div>
</article>
	<?php
endwhile;
?>
</div>
<?php else : ?>
<p class="text-[#334155]">Nothing found.</p>
<?php endif; ?>
</div>
<?php
get_footer();
