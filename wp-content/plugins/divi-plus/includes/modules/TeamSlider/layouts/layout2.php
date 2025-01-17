<?php
/**
 * The Template for displaying Layout 2
 *
 * @author      Elicus Technologies <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2022 Elicus Technologies Private Limited
 * @version     1.9.6
 */

$social_icons = '';
if ( 'on' === $show_social_icon ) {
	if (
		'' !== $facebook_url ||
		'' !== $twitter_url ||
		'' !== $linkedin_url ||
		'' !== $instagram_url ||
		'' !== $youtube_url ||
		'' !== $email ||
		'' !== $phone_number
	) {
		$social_icons = sprintf(
			'<div class="dipl_team_social_wrapper">%1$s%2$s%3$s%4$s%5$s%6$s%7$s</div>',
			$facebook_url,
			$twitter_url,
			$linkedin_url,
			$instagram_url,
			$youtube_url,
			$email,
			$phone_number
		);
	}
}

if ( '' !== $skill_bar ) {
	$skill_bar = sprintf(
		'<div class="dipl_skill_bar_wrapper">%1$s</div>',
		$skill_bar
	);
}

$output .= sprintf(
	'<div id="dipl_team_member_%7$s" class="dipl_team_member_card%8$s" data-link="%9$s" data-link_target="%10$s">
		<div class="dipl_team_image_wrapper">%1$s</div>
		<div class="dipl_team_content_wrapper">%2$s%3$s%4$s%5$s%6$s</div>
		%8$s
	</div>',
	$member_image,
	$member_name,
	$designation,
	$short_description,
	$skill_bar,
	$social_icons,
	esc_attr( $post_id ),
	'on' === $enable_member_link ? ' dipl_team_link' : '',
	esc_url( get_permalink( $post_id ) ),
	'on' === $link_target ? '_blank' : '_self'
);