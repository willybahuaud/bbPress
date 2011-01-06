<?php

/**
 * bbPress Forum Template Tags
 *
 * @package bbPress
 * @subpackage TemplateTags
 */

/** START - Forum Loop Functions **********************************************/

/**
 * The main forum loop.
 *
 * WordPress makes this easy for us.
 *
 * @since bbPress (r2464)
 *
 * @param mixed $args All the arguments supported by {@link WP_Query}
 * @uses WP_Query To make query and get the forums
 * @uses current_user_can() To check if the current user is capable of editing
 *                           others' forums
 * @uses apply_filters() Calls 'bbp_has_forums' with
 *                        bbPres::forum_query::have_posts()
 *                        and bbPres::forum_query
 * @return object Multidimensional array of forum information
 */
function bbp_has_forums( $args = '' ) {
	global $wp_query, $bbp;

	$default = array (
		'post_type'      => $bbp->forum_id,
		'post_parent'    => bbp_get_forum_id(),
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC'
	);

	$r = wp_parse_args( $args, $default );

	// Don't show private forums to normal users
	if ( !current_user_can( 'read_private_forums' ) && empty( $r['meta_key'] ) && empty( $r['meta_value'] ) && empty( $r['meta_compare'] ) ) {
		$r['meta_key']     = '_bbp_forum_visibility';
		$r['meta_value']   = 'public';
		$r['meta_compare'] = '==';
	}

	$bbp->forum_query = new WP_Query( $r );

	return apply_filters( 'bbp_has_forums', $bbp->forum_query->have_posts(), $bbp->forum_query );
}

/**
 * Whether there are more forums available in the loop
 *
 * @since bbPress (r2464)
 *
 * @uses bbPress:forum_query::have_posts() To check if there are more forums
 *                                          available
 * @return object Forum information
 */
function bbp_forums() {
	global $bbp;
	return $bbp->forum_query->have_posts();
}

/**
 * Loads up the current forum in the loop
 *
 * @since bbPress (r2464)
 *
 * @uses bbPress:forum_query::the_post() To get the current forum
 * @return object Forum information
 */
function bbp_the_forum() {
	global $bbp;
	return $bbp->forum_query->the_post();
}

/** FORUM *********************************************************************/

/**
 * Output forum id
 *
 * @since bbPress (r2464)
 *
 * @param $forum_id Optional. Used to check emptiness
 * @uses bbp_get_forum_id() To get the forum id
 */
function bbp_forum_id( $forum_id = 0 ) {
	echo bbp_get_forum_id( $forum_id );
}
	/**
	 * Return the forum id
	 *
	 * @since bbPress (r2464)
	 *
	 * @param $forum_id Optional. Used to check emptiness
	 * @uses bbPress::forum_query::in_the_loop To check if we're in the loop
	 * @uses bbPress::forum_query::post::ID To get the forum id
	 * @uses WP_Query::post::ID To get the forum id
	 * @uses bbp_is_forum() To check if it's a forum page
	 * @uses bbp_is_topic() To check if it's a topic page
	 * @uses bbp_get_topic_forum_id() To get the topic forum id
	 * @uses apply_filters() Calls 'bbp_get_forum_id' with the forum id
	 * @return int Forum id
	 */
	function bbp_get_forum_id( $forum_id = 0 ) {
		global $bbp, $wp_query;

		// Easy empty checking
		if ( !empty( $forum_id ) && is_numeric( $forum_id ) )
			$bbp_forum_id = $forum_id;

		// Currently inside a forum loop
		elseif ( !empty( $bbp->forum_query->in_the_loop ) && isset( $bbp->forum_query->post->ID ) )
			$bbp_forum_id = $bbp->forum_query->post->ID;

		// Currently viewing a forum
		elseif ( bbp_is_forum() && isset( $wp_query->post->ID ) )
			$bbp_forum_id = $wp_query->post->ID;

		// Currently viewing a topic
		elseif ( bbp_is_topic() )
			$bbp_forum_id = bbp_get_topic_forum_id();

		// Fallback
		else
			$bbp_forum_id = 0;

		// Set global
		$bbp->current_forum_id = $bbp_forum_id;

		return apply_filters( 'bbp_get_forum_id', (int) $bbp_forum_id );
	}

/**
 * Output the link to the forum
 *
 * @since bbPress (r2464)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_permalink() To get the permalink
 */
function bbp_forum_permalink( $forum_id = 0 ) {
	echo bbp_get_forum_permalink( $forum_id );
}
	/**
	 * Return the link to the forum
	 *
	 * @since bbPress (r2464)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses get_permalink() Get the permalink of the forum
	 * @uses apply_filters() Calls 'bbp_get_forum_permalink' with the forum
	 *                        link
	 * @return string Permanent link to forum
	 */
	function bbp_get_forum_permalink( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );
		return apply_filters( 'bbp_get_forum_permalink', get_permalink( $forum_id ) );
	}

/**
 * Output the title of the forum in the loop
 *
 * @since bbPress (r2464)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_title() To get the forum title
 */
function bbp_forum_title( $forum_id = 0 ) {
	echo bbp_get_forum_title( $forum_id );
}
	/**
	 * Return the title of the forum in the loop
	 *
	 * @since bbPress (r2464)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses get_the_title() To get the forum title
	 * @uses apply_filters() Calls 'bbp_get_forum_title' with the title
	 * @return string Title of forum
	 */
	function bbp_get_forum_title( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );

		return apply_filters( 'bbp_get_forum_title', get_the_title( $forum_id ) );
	}

/**
 * Output the forums last update date/time (aka freshness)
 *
 * @since bbPress (r2464)
 *
 * @uses bbp_get_forum_last_active() To get the forum freshness
 * @param int $forum_id Optional. Forum id
 */
function bbp_forum_last_active( $forum_id = 0 ) {
	echo bbp_get_forum_last_active( $forum_id );
}
	/**
	 * Return the forums last update date/time (aka freshness)
	 *
	 * @since bbPress (r2464)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses get_post_meta() To retrieve forum last active meta
	 * @uses bbp_get_forum_last_reply_id() To get forum's last reply id
	 * @uses get_post_field() To get the post date of the reply
	 * @uses bbp_get_forum_last_topic_id() To get forum's last topic id
	 * @uses bbp_get_topic_last_active() To get time when the topic was
	 *                                    last active
	 * @uses bbp_convert_date() To convert the date
	 * @uses bbp_get_time_since() To get time in since format
	 * @uses apply_filters() Calls 'bbp_get_forum_last_active' with last
	 *                        active time and forum id
	 * @return string Forum last update date/time (freshness)
	 */
	function bbp_get_forum_last_active( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );

		if ( !$last_active = get_post_meta( $forum_id, '_bbp_forum_last_active', true ) ) {
			if ( $reply_id = bbp_get_forum_last_reply_id( $forum_id ) ) {
				$last_active = get_post_field( 'post_date', $reply_id );
			} else {
				if ( $topic_id = bbp_get_forum_last_topic_id( $forum_id ) ) {
					$last_active = bbp_get_topic_last_active( $topic_id );
				}
			}
		}

		$last_active = !empty( $last_active ) ? bbp_get_time_since( bbp_convert_date( $last_active ) ) : '';

		return apply_filters( 'bbp_get_forum_last_active', $last_active, $forum_id );
	}

/**
 * Output link to the most recent activity inside a forum.
 *
 * Outputs a complete link with attributes and content.
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_freshness_link() To get the forum freshness link
 */
function bbp_forum_freshness_link( $forum_id = 0) {
	echo bbp_get_forum_freshness_link( $forum_id );
}
	/**
	 * Returns link to the most recent activity inside a forum.
	 *
	 * Returns a complete link with attributes and content.
	 *
	 * @since bbPress (r2625)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses bbp_get_forum_last_reply_url() To get the forum last reply url
	 * @uses bbp_get_forum_last_reply_title() To get the forum last reply
	 *                                         title
	 * @uses bbp_get_forum_last_active() To get the time when the forum was
	 *                                    last active
	 * @uses apply_filters() Calls 'bbp_get_forum_freshness_link' with the
	 *                        link and forum id
	 */
	function bbp_get_forum_freshness_link( $forum_id = 0 ) {
		$forum_id   = bbp_get_forum_id( $forum_id );
		$link_url   = bbp_get_forum_last_reply_url( $forum_id );
		$title      = bbp_get_forum_last_reply_title( $forum_id );
		$time_since = bbp_get_forum_last_active( $forum_id );

		if ( !empty( $time_since ) )
			$anchor = '<a href="' . $link_url . '" title="' . esc_attr( $title ) . '">' . $time_since . '</a>';
		else
			$anchor = __( 'No Topics', 'bbpress' );

		return apply_filters( 'bbp_get_forum_freshness_link', $anchor, $forum_id );
	}

/**
 * Return ID of forum parent, if exists
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_id() To get the forum id
 * @uses get_post_field() To get the forum parent
 * @uses apply_filters() Calls 'bbp_get_forum_parent' with the parent & forum id
 * @return int Forum parent
 */
function bbp_get_forum_parent( $forum_id = 0 ) {
	$forum_id = bbp_get_forum_id( $forum_id );
	return apply_filters( 'bbp_get_forum_parent', (int) get_post_field( 'post_parent', $forum_id ), $forum_id );
}

/**
 * Return array of parent forums
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_id() To get the forum id
 * @uses get_post() To get the forum
 * @uses apply_filters() Calls 'bbp_get_forum_ancestors' with the ancestors
 *                        and forum id
 * @return array Forum ancestors
 */
function bbp_get_forum_ancestors( $forum_id = 0 ) {
	$forum_id = bbp_get_forum_id( $forum_id );

	if ( $forum = get_post( $forum_id ) ) {
		$ancestors = array();
		while ( 0 !== $forum->post_parent ) {
			$ancestors[] = $forum->post_parent;
			$forum       = get_post( $forum->post_parent );
		}
	}

	return apply_filters( 'bbp_get_forum_ancestors', $ancestors, $forum_id );
}

/**
 * Return subforums of given forum
 *
 * @since bbPress (r2747)
 *
 * @param mixed $args All the arguments supported by {@link WP_Query}
 * @uses bbp_get_forum_id() To get the forum id
 * @uses current_user_can() To check if the current user is capable of
 *                           reading private forums
 * @uses get_posts() To get the subforums
 * @uses apply_filters() Calls 'bbp_forum_has_subforums' with the subforums
 *                        and the args
 * @return mixed false if none, array of subs if yes
 */
function bbp_forum_has_subforums( $args = '' ) {
	global $bbp;

	if ( is_numeric( $args ) )
		$args = array( 'post_parent' => $args );

	$default = array(
		'post_parent' => 0,
		'post_type'   => $bbp->forum_id,
		'sort_column' => 'menu_order, post_title'
	);

	$r = wp_parse_args( $args, $default );

	$r['post_parent'] = bbp_get_forum_id( $r['post_parent'] );

	// Don't show private forums to normal users
	if ( !current_user_can( 'read_private_forums' ) && empty( $r['meta_key'] ) && empty( $r['meta_value'] ) && empty( $r['meta_compare'] ) ) {
		$r['meta_key']     = '_bbp_forum_visibility';
		$r['meta_value']   = 'public';
		$r['meta_compare'] = '==';
	}

	// No forum passed
	$sub_forums = !empty( $r['post_parent'] ) ? get_posts( $r ) : '';

	return apply_filters( 'bbp_forum_has_sub_forums', (array) $sub_forums, $args );
}

/**
 * Output a list of forums (can be used to list subforums)
 *
 * @todo - Implement reply counts.
 *
 * @param mixed $args The function supports these args:
 *  - before: To put before the output. Defaults to '<ul class="bbp-forums">'
 *  - after: To put after the output. Defaults to '</ul>'
 *  - link_before: To put before every link. Defaults to '<li class="bbp-forum">'
 *  - link_after: To put after every link. Defaults to '</li>'
 *  - separator: Separator. Defaults to ', '
 *  - forum_id: Forum id. Defaults to ''
 *  - show_topic_count - To show forum topic count or not. Defaults to true
 *  - show_reply_count - To show forum reply count or not. Defaults to true
 * @uses bbp_forum_has_subforums() To check if the forum has subforums or not
 * @uses bbp_get_forum_permalink() To get forum permalink
 * @uses bbp_get_forum_title() To get forum title
 * @uses bbp_is_forum_category() To check if a forum is a category
 * @uses bbp_get_forum_topic_count() To get forum topic count
 * @uses bbp_get_forum_reply_count() To get forum reply count
 */
function bbp_list_forums( $args = '' ) {
	global $bbp;

	// Define used variables
	$output = $sub_forums = $topic_count = $reply_count = '';
	$i = 0;

	// Defaults and arguments
	$defaults = array (
		'before'            => '<ul class="bbp-forums">',
		'after'             => '</ul>',
		'link_before'       => '<li class="bbp-forum">',
		'link_after'        => '</li>',
		'separator'         => ', ',
		'forum_id'          => '',
		'show_topic_count'  => true,
		'show_reply_count'  => true,
	);
	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	// Loop through forums and create a list
	if ( $sub_forums = bbp_forum_has_subforums( $forum_id ) ) {
		// Total count (for separator)
		$total_subs = count( $sub_forums );
		foreach( $sub_forums as $sub_forum ) {
			$i++; // Separator count

			// Get forum details
			$show_sep  = $total_subs > $i ? $separator : '';
			$permalink = bbp_get_forum_permalink( $sub_forum->ID );
			$title     = bbp_get_forum_title( $sub_forum->ID );

			// Show topic and reply counts
			if ( !empty( $show_topic_count ) && !bbp_is_forum_category( $sub_forum->ID ) )
				$topic_count = ' (' . bbp_get_forum_topic_count( $sub_forum->ID ) . ')';

			//if ( !empty( $show_reply_count ) && !bbp_is_forum_category( $sub_forum->ID ) )
			//	$reply_count = ' (' . bbp_get_forum_reply_count( $sub_forum->ID ) . ')';

			$output .= $link_before . '<a href="' . $permalink . '" class="bbp-forum-link">' . $title . $topic_count . $reply_count . '</a>' . $show_sep . $link_after;
		}

		// Output the list
		echo $before . $output . $after;
	}
}

/** FORUM LAST TOPIC **********************************************************/

/**
 * Output the forum's last topic id
 *
 * @since bbPress (r2464)
 *
 * @uses bbp_get_forum_last_active() To get the forum's last topic id
 * @param int $forum_id Optional. Forum id
 */
function bbp_forum_last_topic_id( $forum_id = 0 ) {
	echo bbp_get_forum_last_topic_id( $forum_id );
}
	/**
	 * Return the forum's last topic id
	 *
	 * @since bbPress (r2464)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses get_post_meta() To get the forum's last topic id
	 * @uses apply_filters() Calls 'bbp_get_forum_last_topic_id' with the
	 *                        forum and topic id
	 * @return int Forum's last topic id
	 */
	function bbp_get_forum_last_topic_id( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );
		$topic_id = get_post_meta( $forum_id, '_bbp_forum_last_topic_id', true );

		return apply_filters( 'bbp_get_forum_last_topic_id', $topic_id, $forum_id );
	}

/**
 * Output the title of the last topic inside a forum
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_last_topic_title() To get the forum's last topic's title
 */
function bbp_forum_last_topic_title( $forum_id = 0 ) {
	echo bbp_get_forum_last_topic_title( $forum_id );
}
	/**
	 * Return the title of the last topic inside a forum
	 *
	 * @since bbPress (r2625)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses bbp_get_forum_last_topic_id() To get the forum's last topic id
	 * @uses bbp_get_topic_title() To get the topic's title
	 * @uses apply_filters() Calls 'bbp_get_forum_last_topic_title' with the
	 *                        topic title and forum id
	 * @return string Forum's last topic's title
	 */
	function bbp_get_forum_last_topic_title( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );
		return apply_filters( 'bbp_get_forum_last_topic_title', bbp_get_topic_title( bbp_get_forum_last_topic_id( $forum_id ) ), $forum_id );
	}

/**
 * Output the link to the last topic in a forum
 *
 * @since bbPress (r2464)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_last_topic_permalink() To get the forum's last topic's
 *                                             permanent link
 */
function bbp_forum_last_topic_permalink( $forum_id = 0 ) {
	echo bbp_get_forum_last_topic_permalink( $forum_id );
}
	/**
	 * Return the link to the last topic in a forum
	 *
	 * @since bbPress (r2464)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses bbp_get_forum_last_topic_id() To get the forum's last topic id
	 * @uses bbp_get_topic_permalink() To get the topic's permalink
	 * @uses apply_filters() Calls 'bbp_get_forum_last_topic_permalink' with
	 *                        the topic link and forum id
	 * @return string Permanent link to topic
	 */
	function bbp_get_forum_last_topic_permalink( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );
		return apply_filters( 'bbp_get_forum_last_topic_permalink', bbp_get_topic_permalink( bbp_get_forum_last_topic_id( $forum_id ) ), $forum_id );
	}

/**
 * Return the author ID of the last topic of a forum
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_id() To get the forum id
 * @uses bbp_get_forum_last_topic_id() To get the forum's last topic id
 * @uses bbp_get_topic_author_id() To get the topic's author id
 * @uses apply_filters() Calls 'bbp_get_forum_last_topic_author' with the author
 *                        id and forum id
 * @return int Forum's last topic's author id
 */
function bbp_get_forum_last_topic_author_id( $forum_id = 0 ) {
	$forum_id  = bbp_get_forum_id( $forum_id );
	$author_id = bbp_get_topic_author_id( bbp_get_forum_last_topic_id( $forum_id ) );
	return apply_filters( 'bbp_get_forum_last_topic_author_id', $author_id, $forum_id );
}

/**
 * Output link to author of last topic of forum
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_last_topic_author_link() To get the forum's last topic's
 *                                               author link
 */
function bbp_forum_last_topic_author_link( $forum_id = 0 ) {
	echo bbp_get_forum_last_topic_author_link( $forum_id );
}
	/**
	 * Return link to author of last topic of forum
	 *
	 * @since bbPress (r2625)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses bbp_get_forum_last_topic_author_id() To get the forum's last
	 *                                             topic's author id
	 * @uses bbp_get_user_profile_link() To get the author's profile link
	 * @uses apply_filters() Calls 'bbp_get_forum_last_topic_author_link'
	 *                        with the author link and forum id
	 * @return string Forum's last topic's author link
	 */
	function bbp_get_forum_last_topic_author_link( $forum_id = 0 ) {
		$forum_id    = bbp_get_forum_id( $forum_id );
		$author_id   = bbp_get_forum_last_topic_author_id( $forum_id );
		$author_link = bbp_get_user_profile_link( $author_id );
		return apply_filters( 'bbp_get_forum_last_topic_author_link', $author_link, $forum_id );
	}

/** FORUM LAST REPLY **********************************************************/

/**
 * Output the forums last reply id
 *
 * @since bbPress (r2464)
 *
 * @uses bbp_get_forum_last_reply_id() To get the forum's last reply id
 * @param int $forum_id Optional. Forum id
 */
function bbp_forum_last_reply_id( $forum_id = 0 ) {
	echo bbp_get_forum_last_reply_id( $forum_id );
}
	/**
	 * Return the forums last reply id
	 *
	 * @since bbPress (r2464)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses get_post_meta() To get the forum's last reply id
	 * @uses bbp_update_forum_last_reply_id() To update and get the last
	 *                                         reply id of the forum
	 * @uses apply_filters() Calls 'bbp_get_forum_last_reply_id' with
	 *                        the last reply id and forum id
	 * @return int Forum's last reply id
	 */
	function bbp_get_forum_last_reply_id( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );
		$reply_id = get_post_meta( $forum_id, '_bbp_forum_last_reply_id', true );

		if ( '' === $reply_id )
			$reply_id = bbp_update_forum_last_reply_id( $forum_id );

		return apply_filters( 'bbp_get_forum_last_reply_id', $reply_id, $forum_id );
	}

/**
 * Output the title of the last reply inside a forum
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_last_reply_title() To get the forum's last reply's title
 */
function bbp_forum_last_reply_title( $forum_id = 0 ) {
	echo bbp_get_forum_last_reply_title( $forum_id );
}
	/**
	 * Return the title of the last reply inside a forum
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses bbp_get_forum_last_reply_id() To get the forum's last reply id
	 * @uses bbp_get_reply_title() To get the reply title
	 * @uses apply_filters() Calls 'bbp_get_forum_last_reply_title' with the
	 *                        reply title and forum id
	 * @return string
	 */
	function bbp_get_forum_last_reply_title( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );
		return apply_filters( 'bbp_get_forum_last_reply_title', bbp_get_reply_title( bbp_get_forum_last_reply_id( $forum_id ) ), $forum_id );
	}

/**
 * Output the link to the last reply in a forum
 *
 * @since bbPress (r2464)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_last_reply_permalink() To get the forum last reply link
 */
function bbp_forum_last_reply_permalink( $forum_id = 0 ) {
	echo bbp_get_forum_last_reply_permalink( $forum_id );
}
	/**
	 * Return the link to the last reply in a forum
	 *
	 * @since bbPress (r2464)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses bbp_get_forum_last_reply_id() To get the forum's last reply id
	 * @uses bbp_get_reply_permalink() To get the reply permalink
	 * @uses apply_filters() Calls 'bbp_get_forum_last_reply_permalink' with
	 *                        the reply link and forum id
	 * @return string Permanent link to the forum's last reply
	 */
	function bbp_get_forum_last_reply_permalink( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );
		return apply_filters( 'bbp_get_forum_last_reply_permalink', bbp_get_reply_permalink( bbp_get_forum_last_reply_id( $forum_id ) ), $forum_id );
	}

/**
 * Output the url to the last reply in a forum
 *
 * @since bbPress (r2683)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_last_reply_url() To get the forum last reply url
 */
function bbp_forum_last_reply_url( $forum_id = 0 ) {
	echo bbp_get_forum_last_reply_url( $forum_id );
}
	/**
	 * Return the url to the last reply in a forum
	 *
	 * @since bbPress (r2683)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses bbp_get_forum_last_reply_id() To get the forum's last reply id
	 * @uses bbp_get_reply_url() To get the reply url
	 * @uses bbp_get_forum_last_topic_permalink() To get the forum's last
	 *                                             topic's permalink
	 * @uses apply_filters() Calls 'bbp_get_forum_last_reply_url' with the
	 *                        reply url and forum id
	 * @return string Paginated URL to latest reply
	 */
	function bbp_get_forum_last_reply_url( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );

		// If forum has replies, get the last reply and use its url
		if ( $reply_id = bbp_get_forum_last_reply_id( $forum_id ) ) {
			$reply_url = bbp_get_reply_url( $reply_id );

		// No replies, so look for topics and use last permalink
		} else {
			if ( !$reply_url = bbp_get_forum_last_topic_permalink( $forum_id ) ) {
				// No topics either, so set $reply_url as empty
				$reply_url = '';
			}
		}

		// Filter and return
		return apply_filters( 'bbp_get_forum_last_reply_url', $reply_url, $forum_id );
	}

/**
 * Output author ID of last reply of forum
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_last_reply_author_id() To get the forum's last reply
 *                                             author id
 */
function bbp_forum_last_reply_author_id( $forum_id = 0 ) {
	echo bbp_get_forum_last_reply_author_id( $forum_id );
}
	/**
	 * Return author ID of last reply of forum
	 *
	 * @since bbPress (r2625)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses bbp_get_forum_last_reply_author_id() To get the forum's last
	 *                                             reply's author id
	 * @uses bbp_get_reply_author_id() To get the reply's author id
	 * @uses apply_filters() Calls 'bbp_get_forum_last_reply_author_id' with
	 *                        the author id and forum id
	 * @return int Forum's last reply author id
	 */
	function bbp_get_forum_last_reply_author_id( $forum_id = 0 ) {
		$forum_id  = bbp_get_forum_id( $forum_id );
		$author_id = bbp_get_reply_author_id( bbp_get_forum_last_reply_id( $forum_id ) );
		return apply_filters( 'bbp_get_forum_last_reply_author_id', $author_id, $forum_id );
	}

/**
 * Output link to author of last reply of forum
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_last_reply_author_link() To get the forum's last reply's
 *                                               author link
 */
function bbp_forum_last_reply_author_link( $forum_id = 0 ) {
	echo bbp_get_forum_last_reply_author_link( $forum_id );
}
	/**
	 * Return link to author of last reply of forum
	 *
	 * @since bbPress (r2625)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses bbp_get_forum_last_reply_author_id() To get the forum's last
	 *                                             reply's author id
	 * @uses bbp_get_user_profile_link() To get the reply's author's profile
	 *                                    link
	 * @uses apply_filters() Calls 'bbp_get_forum_last_reply_author_link'
	 *                        with the author link and forum id
	 * @return string Link to author of last reply of forum
	 */
	function bbp_get_forum_last_reply_author_link( $forum_id = 0 ) {
		$forum_id    = bbp_get_forum_id( $forum_id );
		$author_id   = bbp_get_forum_last_reply_author_id( $forum_id );
		$author_link = bbp_get_user_profile_link( $author_id );
		return apply_filters( 'bbp_get_forum_last_reply_author_link', $author_link, $forum_id );
	}

/** FORUM COUNTS **************************************************************/

/**
 * Output total sub-forum count of a forum
 *
 * @since bbPress (r2464)
 *
 * @uses bbp_get_forum_subforum_count() To get the forum's subforum count
 * @param int $forum_id Optional. Forum id to check
 */
function bbp_forum_subforum_count( $forum_id = 0 ) {
	echo bbp_get_forum_subforum_count( $forum_id );
}
	/**
	 * Return total subforum count of a forum
	 *
	 * @since bbPress (r2464)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses get_post_meta() To get the subforum count
	 * @uses bbp_update_forum_subforum_count() To update the forum's
	 *                                          subforum count if needed
	 * @uses apply_filters() Calls 'bbp_get_forum_subforum_count' with the
	 *                        subforum count and forum id
	 * @return int Forum's subforum count
	 */
	function bbp_get_forum_subforum_count( $forum_id = 0 ) {
		$forum_id    = bbp_get_forum_id( $forum_id );
		$forum_count = get_post_meta( $forum_id, '_bbp_forum_subforum_count', true );

		if ( '' === $forum_count )
			$forum_count = bbp_update_forum_subforum_count( $forum_id );

		return apply_filters( 'bbp_get_forum_subforum_count', (int) $forum_count, $forum_id );
	}

/**
 * Output total topic count of a forum
 *
 * @since bbPress (r2464)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_topic_count() To get the forum topic count
 */
function bbp_forum_topic_count( $forum_id = 0 ) {
	echo bbp_get_forum_topic_count( $forum_id );
}
	/**
	 * Return total topic count of a forum
	 *
	 * @since bbPress (r2464)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses get_post_meta() To get the forum topic count
	 * @uses bbp_update_forum_topic_count() To update the topic count if
	 *                                       needed
	 * @uses apply_filters() Calls 'bbp_get_forum_topic_count' with the
	 *                        topic count and forum id
	 * @return int Forum topic count
	 */
	function bbp_get_forum_topic_count( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );
		$topics   = get_post_meta( $forum_id, '_bbp_forum_topic_count', true );

		if ( '' === $topics )
			$topics = bbp_update_forum_topic_count( $forum_id );

		return apply_filters( 'bbp_get_forum_topic_count', (int) $topics, $forum_id );
	}

/**
 * Output total reply count of a forum
 *
 * @since bbPress (r2464)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_reply_count() To get the forum reply count
 */
function bbp_forum_reply_count( $forum_id = 0 ) {
	echo bbp_get_forum_reply_count( $forum_id );
}
	/**
	 * Return total post count of a forum
	 *
	 * @since bbPress (r2464)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses get_post_meta() To get the forum reply count
	 * @uses bbp_update_forum_reply_count() To update the reply count if
	 *                                       needed
	 * @uses apply_filters() Calls 'bbp_get_forum_reply_count' with the
	 *                        reply count and forum id
	 * @return int Forum reply count
	 */
	function bbp_get_forum_reply_count( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );
		$replies  = get_post_meta( $forum_id, '_bbp_forum_reply_count', true );

		if ( '' === $replies )
			$replies = bbp_update_forum_reply_count( $forum_id );

		return apply_filters( 'bbp_get_forum_reply_count', (int) $replies, $forum_id );
	}

/**
 * Output total voice count of a forum
 *
 * @since bbPress (r2567)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_voice_count() To get the forum voice count
 */
function bbp_forum_voice_count( $forum_id = 0 ) {
	echo bbp_get_forum_voice_count( $forum_id );
}
	/**
	 * Return total voice count of a forum
	 *
	 * @since bbPress (r2567)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses get_post_meta() To get the forum voice count
	 * @uses bbp_update_forum_voice_count() To update the voice count if
	 *                                       needed
	 * @uses apply_filters() Calls 'bbp_get_forum_voice_count' with the
	 *                        voice count and forum id
	 * @return int Forum voice count
	 */
	function bbp_get_forum_voice_count( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );
		$voices   = get_post_meta( $forum_id, '_bbp_forum_voice_count', true );

		if ( '' === $voices )
			$voices = bbp_update_forum_voice_count( $forum_id );

		return apply_filters( 'bbp_get_forum_voice_count', (int) $voices, $forum_id );
	}

/**
 * Output the status of the forum
 *
 * @since bbPress (r2667)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_status() To get the forum status
 */
function bbp_forum_status( $forum_id = 0 ) {
	echo bbp_get_forum_status( $forum_id );
}
	/**
	 * Return the status of the forum
	 *
	 * @since bbPress (r2667)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @uses bbp_get_forum_id() To get the forum id
	 * @uses get_post_status() To get the forum's status
	 * @uses apply_filters() Calls 'bbp_get_forum_status' with the status
	 *                        and forum id
	 * @return string Status of forum
	 */
	function bbp_get_forum_status( $forum_id = 0 ) {
		$forum_id = bbp_get_forum_id( $forum_id );

		return apply_filters( 'bbp_get_forum_status', get_post_meta( $forum_id, '_bbp_forum_status', true ) );
	}

/**
 * Closes a forum
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id forum id
 * @uses wp_get_single_post() To get the forum
 * @uses do_action() Calls 'bbp_close_forum' with the forum id
 * @uses add_post_meta() To add the previous status to a meta
 * @uses wp_insert_post() To update the forum with the new status
 * @uses do_action() Calls 'bbp_opened_forum' with the forum id
 * @return mixed False or {@link WP_Error} on failure, forum id on success
 */
function bbp_close_forum( $forum_id = 0 ) {
	global $bbp;

	if ( !$forum = wp_get_single_post( $forum_id, ARRAY_A ) )
		return $forum;

	do_action( 'bbp_close_forum', $forum_id );

	update_post_meta( $forum_id, '_bbp_forum_status', 'closed' );

	do_action( 'bbp_closed_forum', $forum_id );

	return $forum_id;
}

/**
 * Opens a forum
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id forum id
 * @uses wp_get_single_post() To get the forum
 * @uses do_action() Calls 'bbp_open_forum' with the forum id
 * @uses get_post_meta() To get the previous status
 * @uses delete_post_meta() To delete the previous status meta
 * @uses wp_insert_post() To update the forum with the new status
 * @uses do_action() Calls 'bbp_opened_forum' with the forum id
 * @return mixed False or {@link WP_Error} on failure, forum id on success
 */
function bbp_open_forum( $forum_id = 0 ) {
	global $bbp;

	if ( !$forum = wp_get_single_post( $forum_id, ARRAY_A ) )
		return $forum;

	do_action( 'bbp_open_forum', $forum_id );

	update_post_meta( $forum_id, '_bbp_forum_status', 'open' );

	do_action( 'bbp_opened_forum', $forum_id );

	return $forum_id;
}

/**
 * Make the forum a category
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id Optional. Forum id
 * @uses update_post_meta() To update the forum category meta
 * @return bool False on failure, true on success
 */
function bbp_categorize_forum( $forum_id = 0 ) {
	return update_post_meta( $forum_id, '_bbp_forum_type', 'category' );
}

/**
 * Remove the category status from a forum
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id Optional. Forum id
 * @uses delete_post_meta() To delete the forum category meta
 * @return bool False on failure, true on success
 */
function bbp_normalize_forum( $forum_id = 0 ) {
	return update_post_meta( $forum_id, '_bbp_forum_type', 'forum' );
}

/**
 * Mark the forum as private
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id Optional. Forum id
 * @uses update_post_meta() To update the forum private meta
 * @return bool False on failure, true on success
 */
function bbp_privatize_forum( $forum_id = 0 ) {
	return update_post_meta( $forum_id, '_bbp_forum_visibility', 'private' );
}

/**
 * Unmark the forum as private
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id Optional. Forum id
 * @uses delete_post_meta() To delete the forum private meta
 * @return bool False on failure, true on success
 */
function bbp_publicize_forum( $forum_id = 0 ) {
	return update_post_meta( $forum_id, '_bbp_forum_visibility', 'public' );
}

/**
 * Is the forum a category?
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id Optional. Forum id
 * @uses get_post_meta() To get the forum category meta
 * @return bool Whether the forum is a category or not
 */
function bbp_is_forum_category( $forum_id = 0 ) {
	$forum_id = bbp_get_forum_id( $forum_id );
	$type     = get_post_meta( $forum_id, '_bbp_forum_type', true );

	if ( !empty( $type ) && 'category' == $type )
		return true;

	return false;
}

/**
 * Is the forum open?
 *
 * @since bbPress (r2746)
 * @param int $forum_id Optional. Forum id
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_is_forum_closed() To check if the forum is closed or not
 * @return bool Whether the forum is open or not
 */
function bbp_is_forum_open( $forum_id = 0 ) {
	return !bbp_is_forum_closed( $forum_id );
}

	/**
	 * Is the forum closed?
	 *
	 * @since bbPress (r2746)
	 *
	 * @param int $forum_id Optional. Forum id
	 * @param bool $check_ancestors Check if the ancestors are closed (only
	 *                               if they're a category)
	 * @uses bbp_get_forum_status() To get the forum status
	 * @uses bbp_get_forum_ancestors() To get the forum ancestors
	 * @uses bbp_is_forum_category() To check if the forum is a category
	 * @uses bbp_is_forum_closed() To check if the forum is closed
	 * @return bool True if closed, false if not
	 */
	function bbp_is_forum_closed( $forum_id = 0, $check_ancestors = true ) {
		global $bbp;

		$forum_id = bbp_get_forum_id( $forum_id );

		if ( $bbp->closed_status_id == bbp_get_forum_status( $forum_id ) )
			return true;

		if ( !empty( $check_ancestors ) ) {
			$ancestors = bbp_get_forum_ancestors( $forum_id );

			foreach ( (array) $ancestors as $ancestor ) {
				if ( bbp_is_forum_category( $ancestor, false ) && bbp_is_forum_closed( $ancestor, false ) )
					return true;
			}
		}

		return false;
	}

/**
 * Is the forum private?
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id Optional. Forum id
 * @param bool $check_ancestors Check if the ancestors are private (only if
 *                               they're a category)
 * @uses get_post_meta() To get the forum private meta
 * @uses bbp_get_forum_ancestors() To get the forum ancestors
 * @uses bbp_is_forum_category() To check if the forum is a category
 * @uses bbp_is_forum_closed() To check if the forum is closed
 * @return bool True if closed, false if not
 */
function bbp_is_forum_private( $forum_id = 0, $check_ancestors = true ) {
	global $bbp;

	$forum_id   = bbp_get_forum_id( $forum_id );
	$visibility = get_post_meta( $forum_id, '_bbp_forum_visibility', true );

	if ( !empty( $visibility ) && 'private' == $visibility )
		return true;

	if ( !empty( $check_ancestors ) ) {
		$ancestors = bbp_get_forum_ancestors( $forum_id );

		foreach ( (array) $ancestors as $ancestor ) {
			if ( bbp_is_forum_private( $ancestor, false ) )
				return true;
		}
	}

	return false;
}

/**
 * Output the row class of a forum
 *
 * @since bbPress (r2667)
 *
 * @uses bbp_get_forum_class() To get the row class of the forum
 */
function bbp_forum_class() {
	echo bbp_get_forum_class();
}
	/**
	 * Return the row class of a forum
	 *
	 * @since bbPress (r2667)
	 *
	 * @uses post_class() To get all the classes including ours
	 * @uses apply_filters() Calls 'bbp_get_forum_class' with the classes
	 * @return string Row class of the forum
	 */
	function bbp_get_forum_class() {
		global $bbp;

		$classes   = array();
		$classes[] = $bbp->forum_query->current_post % 2 ? 'even' : 'odd';
		$classes[] = bbp_is_forum_category() ? 'status-category' : '';
		$classes[] = bbp_is_forum_private()  ? 'status-private'  : '';
		$classes   = array_filter( $classes );

		$post      = post_class( $classes );

		return apply_filters( 'bbp_get_forum_class', $post );
	}

/** Forum Updaters ************************************************************/

/**
 * Update the forum last topic id
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @param int $topic_id Optional. Topic id
 * @uses bbp_get_forum_id() To get the forum id
 * @uses bbp_get_topic_id() To get the topic id
 * @uses update_post_meta() To update the forum's last topic id meta
 * @return bool True on success, false on failure
 */
function bbp_update_forum_last_topic_id( $forum_id = 0, $topic_id = 0 ) {
	$forum_id = bbp_get_forum_id( $forum_id );
	$topic_id = bbp_get_topic_id( $topic_id );

	// Update the last topic ID
	if ( !empty( $topic_id ) )
		return update_post_meta( $forum_id, '_bbp_forum_last_topic_id', $topic_id );

	return false;
}

/**
 * Update the forum last reply id
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @param int $reply_id Optional. Reply id
 * @uses bbp_get_forum_id() To get the forum id
 * @uses bbp_get_reply_id() To get the reply id
 * @uses update_post_meta() To update the forum's last reply id meta
 * @return bool True on success, false on failure
 */
function bbp_update_forum_last_reply_id( $forum_id = 0, $reply_id = 0 ) {
	$forum_id = bbp_get_forum_id( $forum_id );
	$reply_id = bbp_get_reply_id( $reply_id );

	// Update the last reply ID
	if ( !empty( $reply_id ) )
		return update_post_meta( $forum_id, '_bbp_forum_last_reply_id', $reply_id );

	return false;
}

/**
 * Update the forums last active date/time (aka freshness)
 *
 * @since bbPress (r2680)
 *
 * @param int $forum_id Optional. Forum id
 * @param string $new_time Optional. New time in mysql format
 * @uses bbp_get_forum_id() To get the forum id
 * @uses current_time() To get the current time
 * @uses update_post_meta() To update the forum's last active meta
 * @return bool True on success, false on failure
 */
function bbp_update_forum_last_active( $forum_id = 0, $new_time = '' ) {
	$forum_id = bbp_get_forum_id( $forum_id );

	// Check time and use current if empty
	if ( empty( $new_time ) )
		$new_time = current_time( 'mysql' );

	// Update last active
	if ( !empty( $forum_id ) )
		return update_post_meta( $forum_id, '_bbp_forum_last_active', $new_time );

	return false;
}

/**
 * Update the forum sub-forum count
 *
 * @todo Make this work.
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_id() To get the forum id
 * @return bool True on success, false on failure
 */
function bbp_update_forum_subforum_count( $forum_id = 0 ) {
	$forum_id = bbp_get_forum_id( $forum_id );

	return false;
}

/**
 * Adjust the total topic count of a forum
 *
 * @since bbPress (r2464)
 *
 * @param int $forum_id Optional. Forum id or topic id. It is checked whether it
 *                       is a topic or a forum. If it's a topic, its parent,
 *                       i.e. the forum is automatically retrieved.
 * @uses get_post_field() To check whether the supplied id is a topic
 * @uses bbp_get_topic_forum_id() To get the topic's forum id
 * @uses wpdb::prepare() To prepare the sql statement
 * @uses wpdb::get_col() To execute the query and get the column back
 * @uses update_post_meta() To update the forum's topic count meta
 * @uses apply_filters() Calls 'bbp_update_forum_topic_count' with the topic
 *                        count and forum id
 * @return int Forum topic count
 */
function bbp_update_forum_topic_count( $forum_id = 0 ) {
	global $wpdb, $bbp;

	$forum_id = bbp_get_forum_id( $forum_id );

	// If it's a reply, then get the parent (topic id)
	if ( $bbp->topic_id == get_post_field( 'post_type', $forum_id ) )
		$forum_id = bbp_get_topic_forum_id( $forum_id );

	// Get topics count
	$topics = count( $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_status = 'publish' AND post_type = '" . $bbp->topic_id . "';", $forum_id ) ) );

	// Update the count
	update_post_meta( $forum_id, '_bbp_forum_topic_count', (int) $topics );

	return apply_filters( 'bbp_update_forum_topic_count', (int) $topics, $forum_id );
}

/**
 * Adjust the total reply count of a forum
 *
 * @todo Make this work
 *
 * @since bbPress (r2464)
 *
 * @param int $forum_id Optional. Forum id or reply id. It is checked whether it
 *                       is a reply or a forum. If it's a reply, its forum is
 *                       automatically retrieved.
 * @uses get_post_field() To check whether the supplied id is a reply
 * @uses bbp_get_reply_topic_id() To get the reply's topic id
 * @uses bbp_get_topic_forum_id() To get the topic's forum id
 * @uses wpdb::prepare() To prepare the sql statement
 * @uses wpdb::get_col() To execute the query and get the column back
 * @uses update_post_meta() To update the forum's reply count meta
 * @uses apply_filters() Calls 'bbp_update_forum_reply_count' with the reply
 *                        count and forum id
 * @return int Forum reply count
 */
function bbp_update_forum_reply_count( $forum_id = 0 ) {
	global $wpdb, $bbp;

	$forum_id = bbp_get_forum_id( $forum_id );

	// If it's a reply, then get the parent (topic id)
	if ( $bbp->reply_id == get_post_field( 'post_type', $forum_id ) ) {
		$topic_id = bbp_get_reply_topic_id( $forum_id );
		$forum_id = bbp_get_topic_forum_id( $topic_id );
	}

	// There should always be at least 1 voice
	$replies = count( $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_status = 'publish' AND post_type = '" . $bbp->reply_id . "';", $forum_id ) ) );

	// Update the count
	update_post_meta( $forum_id, '_bbp_forum_reply_count', (int) $replies );

	return apply_filters( 'bbp_update_forum_reply_count', (int) $replies, $forum_id );
}

/**
 * Adjust the total voice count of a forum
 *
 * @since bbPress (r2567)
 *
 * @param int $forum_id Optional. Forum, topic or reply id. The forum is
 *                                 automatically retrieved based on the input.
 * @uses get_post_field() To check whether the supplied id is a reply
 * @uses bbp_get_reply_topic_id() To get the reply's topic id
 * @uses bbp_get_topic_forum_id() To get the topic's forum id
 * @uses wpdb::prepare() To prepare the sql statement
 * @uses wpdb::get_col() To execute the query and get the column back
 * @uses update_post_meta() To update the forum's voice count meta
 * @uses apply_filters() Calls 'bbp_update_forum_voice_count' with the voice
 *                        count and forum id
 * @return int Forum voice count
 */
function bbp_update_forum_voice_count( $forum_id = 0 ) {
	global $wpdb, $bbp;

	$forum_id = bbp_get_forum_id( $forum_id );

	// If it's a reply, then get the parent (topic id)
	if ( $bbp->reply_id == get_post_field( 'post_type', $forum_id ) )
		$forum_id = bbp_get_reply_topic_id( $forum_id );

	// If it's a topic, then get the parent (forum id)
	if ( $bbp->topic_id == get_post_field( 'post_type', $forum_id ) )
		$forum_id = bbp_get_topic_forum_id( $forum_id );

	// There should always be at least 1 voice
	if ( !$voices = count( $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_author FROM $wpdb->posts WHERE ( post_parent = %d AND post_status = 'publish' AND post_type = '" . $bbp->reply_id . "' ) OR ( ID = %d AND post_type = '" . $bbp->forum_id . "' );", $forum_id, $forum_id ) ) ) )
		$voices = 1;

	// Update the count
	update_post_meta( $forum_id, '_bbp_forum_voice_count', (int) $voices );

	return apply_filters( 'bbp_update_forum_voice_count', (int) $voices, $forum_id );
}

/** END - Forum Loop Functions ************************************************/

?>
