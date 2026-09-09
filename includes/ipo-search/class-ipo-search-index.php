<?php
/**
 * Builds the static search index.
 *
 * The whole point of this module is that searching must not hit the database.
 * Everything the search box needs is baked into one JSON file per language,
 * written to uploads and served as a plain static file.
 *
 * Shape of that file:
 *
 *   home  Site URL, stripped off the front of every link.
 *   up    Uploads URL, stripped off the front of every image.
 *   pr    Upcoming programs  [ title, url, img, subtitle, [dates], venues ]
 *   pp    Past programs      [ title, url, img, subtitle, last_date ]
 *   ar    Artists            [ name, url, img ]
 *   se    Series             [ title, url ]
 *   pg    Pages              [ title, url ]
 *
 * Rows are positional arrays rather than objects, and the two shared prefixes
 * are factored out, because those two things together cut the file roughly in
 * half — and this file is downloaded by every visitor who opens the search.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IPO_Search_Index {

	/** Option holding the current filename per language. */
	const OPTION_FILES = 'ipo_search_index_files';

	/** Option holding the last build report. */
	const OPTION_REPORT = 'ipo_search_index_report';

	/** Option holding whatever killed the last build. */
	const OPTION_ERROR = 'ipo_search_index_error';

	/**
	 * Where the build currently is.
	 *
	 * A fatal takes the whole request down before anything can be returned, so
	 * the only way to learn where it happened is to leave a trail behind as we
	 * go and read it back afterwards.
	 *
	 * @var string
	 */
	protected static $stage = '';

	/**
	 * @param string $name Stage name.
	 */
	protected static function stage( $name ) {
		self::$stage = $name;
	}

	/**
	 * Record a fatal that PHP will not let us catch.
	 *
	 * Registered as a shutdown function for the duration of a build. The
	 * database connection is still alive at this point, so the option write
	 * goes through even though the request is dying.
	 */
	public static function capture_fatal() {
		$error = error_get_last();

		if ( ! $error || ! in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ), true ) ) {
			return;
		}

		update_option(
			self::OPTION_ERROR,
			array(
				'when'        => time(),
				'stage'       => self::$stage,
				'kind'        => 'fatal',
				'message'     => $error['message'],
				'file'        => $error['file'],
				'line'        => $error['line'],
				'peak_memory' => size_format( memory_get_peak_usage( true ) ),
				'limit'       => ini_get( 'memory_limit' ),
			),
			false
		);
	}

	/** Folder under uploads. */
	const DIR = 'ipo-search';

	/** Cron hook for the nightly rebuild. */
	const CRON_HOOK = 'ipo_search_rebuild_index';

	/**
	 * Languages to build. Falls back to a single unnamed index when WPML is off.
	 *
	 * @return array<int, string>
	 */
	public static function languages() {
		if ( ! function_exists( 'icl_get_languages' ) ) {
			return array( '' );
		}

		$languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );

		if ( ! is_array( $languages ) || empty( $languages ) ) {
			return array( '' );
		}

		return array_keys( $languages );
	}

	/**
	 * Build every language and swap the live files over.
	 *
	 * @return array<string, mixed> Report.
	 */
	public static function build_all() {
		// A build walks a few thousand posts. Give it room rather than letting it
		// die halfway through and take the request down with a critical error.
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 );
		}
		wp_raise_memory_limit( 'admin' );

		register_shutdown_function( array( __CLASS__, 'capture_fatal' ) );
		self::stage( 'start' );

		$started = microtime( true );
		$files   = array();
		$counts  = array();

		$original = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '';

		foreach ( self::languages() as $lang ) {

			if ( $lang && function_exists( 'do_action' ) ) {
				self::stage( 'switch-language:' . $lang );
				do_action( 'wpml_switch_language', $lang );
			}

			$data = self::collect( $lang );

			self::stage( 'write:' . $lang );
			$file = self::write( $data, $lang );

			if ( $file ) {
				$files[ $lang ]  = $file;
				$counts[ $lang ] = array(
					'upcoming' => count( $data['pr'] ),
					'past'     => count( $data['pp'] ),
					'artists'  => count( $data['ar'] ),
					'series'   => count( $data['se'] ),
					'pages'    => count( $data['pg'] ),
				);
			}
		}

		if ( $original ) {
			do_action( 'wpml_switch_language', $original );
		}

		// Only publish the new filenames once every language wrote successfully,
		// so a half-finished build can never leave the front end pointing at a
		// file that does not exist.
		if ( $files ) {
			update_option( self::OPTION_FILES, $files, false );
			self::prune_old_files( $files );
		}

		$report = array(
			'built'   => time(),
			'seconds' => round( microtime( true ) - $started, 2 ),
			'counts'  => $counts,
			'files'   => $files,
		);

		update_option( self::OPTION_REPORT, $report, false );

		// Got here, so nothing exploded: clear whatever the last failure was.
		delete_option( self::OPTION_ERROR );
		self::stage( 'done' );

		return $report;
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function last_error() {
		$error = get_option( self::OPTION_ERROR, array() );
		return is_array( $error ) ? $error : array();
	}

	/**
	 * Gather everything for one language.
	 *
	 * @param string $lang Language code.
	 * @return array<string, mixed>
	 */
	protected static function collect( $lang ) {
		self::stage( "event-dates" );
		$dates = self::event_dates_by_program();

		$data = array(
			'v'     => 1,
			'built' => gmdate( 'Y-m-d H:i', time() ),
			'lang'  => $lang,
			'home'  => trailingslashit( home_url() ),
			'up'    => trailingslashit( wp_upload_dir()['baseurl'] ),
			'pr'    => array(),
			'pp'    => array(),
			'ar'    => array(),
			'se'    => array(),
			'pg'    => array(),
		);

		self::stage( "programs" );
		self::collect_programs( $data, $dates );
		self::stage( "artists" );
		self::collect_artists( $data );
		self::stage( "series" );
		self::collect_simple( $data, 'se', 'serie' );
		self::stage( "pages" );
		self::collect_simple( $data, 'pg', 'page' );

		return $data;
	}

	/**
	 * Every published event's date, keyed by the program it belongs to.
	 *
	 * One query for the whole site rather than one per program: with ~2,000
	 * events across ~700 programs the per-program version made the build take
	 * minutes instead of seconds.
	 *
	 * @return array<int, array<int, string>> program id => sorted dates
	 */
	protected static function event_dates_by_program() {
		global $wpdb;

		$rows = $wpdb->get_results(
			"SELECT rel.meta_value AS program_id, dt.meta_value AS event_date
			 FROM {$wpdb->postmeta} rel
			 INNER JOIN {$wpdb->postmeta} dt
			         ON dt.post_id = rel.post_id AND dt.meta_key = 'event_date_time'
			 INNER JOIN {$wpdb->posts} p
			         ON p.ID = rel.post_id AND p.post_type = 'event' AND p.post_status = 'publish'
			 WHERE rel.meta_key = 'related_to_program'
			   AND dt.meta_value <> ''
			 ORDER BY dt.meta_value ASC",
			ARRAY_A
		);

		$by_program = array();

		foreach ( (array) $rows as $row ) {
			$program_id = (int) $row['program_id'];
			if ( ! $program_id ) {
				continue;
			}
			$by_program[ $program_id ][] = $row['event_date'];
		}

		return $by_program;
	}

	/**
	 * Programs, split into the ones with dates ahead and the ones without.
	 *
	 * A program is one row carrying all of its upcoming dates, so a concert
	 * running six times shows up once with six dates instead of six times.
	 *
	 * @param array $data  Index being built, by reference.
	 * @param array $dates Dates keyed by program id.
	 */
	protected static function collect_programs( &$data, $dates ) {
		self::stage( 'programs:venues' );
		$venues = self::venues_by_program();

		self::stage( 'programs:batches' );
		$now = time();

		self::each_batch(
			'program',
			function ( $ids ) use ( &$data, $dates, $venues, $now ) {

				foreach ( $ids as $id ) {

					$title = get_the_title( $id );
					if ( '' === trim( $title ) ) {
						continue;
					}

					$program_dates = isset( $dates[ $id ] ) ? $dates[ $id ] : array();
					$upcoming      = array();
					$last_past     = '';

					foreach ( $program_dates as $date ) {
						$stamp = strtotime( $date );
						if ( ! $stamp ) {
							continue;
						}
						if ( $stamp >= $now ) {
							$upcoming[] = gmdate( 'Y-m-d H:i', $stamp );
						} else {
							$last_past = gmdate( 'Y-m-d H:i', $stamp );
						}
					}

					$row = array(
						$title,
						self::relative_link( $id, $data['home'] ),
						self::relative_image( $id, $data['up'] ),
						(string) get_post_meta( $id, 'program_subtitle', true ),
					);

					if ( $upcoming ) {
						$row[] = $upcoming;
						$row[] = isset( $venues[ $id ] ) ? $venues[ $id ] : '';
						$data['pr'][] = $row;
					} else {
						// Nothing ahead: keep it for the "past concerts" toggle, but
						// only if it ever actually happened. Programs with no dates at
						// all are drafts in spirit and would just be noise.
						if ( '' === $last_past ) {
							continue;
						}
						$row[] = $last_past;
						$data['pp'][] = $row;
					}
				}
			}
		);

		// Nearest first for upcoming, most recent first for past.
		usort(
			$data['pr'],
			function ( $a, $b ) {
				return strcmp( $a[4][0], $b[4][0] );
			}
		);

		usort(
			$data['pp'],
			function ( $a, $b ) {
				return strcmp( $b[4], $a[4] );
			}
		);
	}

	/**
	 * @param array $data Index being built, by reference.
	 */
	protected static function collect_artists( &$data ) {
		self::each_batch(
			'artist',
			function ( $ids ) use ( &$data ) {
				foreach ( $ids as $id ) {
					$title = get_the_title( $id );
					if ( '' === trim( $title ) ) {
						continue;
					}

					$data['ar'][] = array(
						$title,
						self::relative_link( $id, $data['home'] ),
						self::relative_image( $id, $data['up'] ),
					);
				}
			}
		);
	}

	/**
	 * Title + link only, for the kinds that do not need a thumbnail.
	 *
	 * @param array  $data Index being built, by reference.
	 * @param string $key  Index key to fill.
	 * @param string $type Post type.
	 */
	protected static function collect_simple( &$data, $key, $type ) {
		self::each_batch(
			$type,
			function ( $ids ) use ( &$data, $key ) {
				foreach ( $ids as $id ) {
					$title = get_the_title( $id );
					if ( '' === trim( $title ) ) {
						continue;
					}

					$data[ $key ][] = array(
						$title,
						self::relative_link( $id, $data['home'] ),
					);
				}
			}
		);
	}

	/**
	 * Venue names for every program at once.
	 *
	 * The first version of this ran get_related_event_ids() plus a term lookup
	 * per program: roughly 3,000 queries across ~700 programs and ~2,000 events,
	 * which ran the build straight into the PHP time limit and took the site
	 * down with a critical error. One join does the same work.
	 *
	 * @return array<int, string> program id => "venue, venue"
	 */
	protected static function venues_by_program() {
		global $wpdb;

		$rows = $wpdb->get_results(
			"SELECT rel.meta_value AS program_id, t.name AS venue
			 FROM {$wpdb->postmeta} rel
			 INNER JOIN {$wpdb->posts} e
			         ON e.ID = rel.post_id AND e.post_type = 'event' AND e.post_status = 'publish'
			 INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = e.ID
			 INNER JOIN {$wpdb->term_taxonomy} tt
			         ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'location'
			 INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
			 WHERE rel.meta_key = 'related_to_program'
			 GROUP BY rel.meta_value, t.name",
			ARRAY_A
		);

		$names = array();

		foreach ( (array) $rows as $row ) {
			$program_id = (int) $row['program_id'];
			if ( ! $program_id ) {
				continue;
			}
			$names[ $program_id ][] = $row['venue'];
		}

		foreach ( $names as $program_id => $list ) {
			$names[ $program_id ] = implode( ', ', $list );
		}

		return $names;
	}

	/**
	 * Walk a post type in batches, handing each batch to a callback.
	 *
	 * Loading every post of a type at once is what breaks on a large site: the
	 * post objects and their meta pile up in the object cache until PHP runs out
	 * of memory. Working a page at a time and dropping each page from the cache
	 * afterwards keeps usage flat no matter how many posts there are, at the cost
	 * of the build simply taking longer.
	 *
	 * Paging stays on get_posts() rather than raw SQL so WPML keeps filtering by
	 * the language currently being built.
	 *
	 * @param string   $type     Post type.
	 * @param callable $callback Receives an array of IDs, with meta and
	 *                           attachments already primed for that batch.
	 * @param int      $size     Batch size.
	 */
	protected static function each_batch( $type, $callback, $size = 200 ) {
		$paged = 1;

		do {
			$ids = get_posts(
				array(
					'post_type'        => $type,
					'post_status'      => 'publish',
					'posts_per_page'   => $size,
					'paged'            => $paged,
					'fields'           => 'ids',
					'orderby'          => 'ID',
					'order'            => 'ASC',
					'no_found_rows'    => true,
					'suppress_filters' => false,
				)
			);

			if ( empty( $ids ) ) {
				return;
			}

			update_meta_cache( 'post', $ids );
			self::prime_attachments( $ids );

			call_user_func( $callback, $ids );

			self::free_posts( $ids );
			++$paged;

		} while ( count( $ids ) === $size );
	}

	/**
	 * Drop a batch of posts from the object cache.
	 *
	 * Without this the cache grows for the whole build and the memory saved by
	 * batching is given straight back.
	 *
	 * @param array $ids Post IDs.
	 */
	protected static function free_posts( $ids ) {
		foreach ( (array) $ids as $id ) {
			wp_cache_delete( $id, 'posts' );
			wp_cache_delete( $id, 'post_meta' );
		}
	}

	/**
	 * Load the attachments a set of posts point at, in one go.
	 *
	 * Without this every relative_image() call fetches its own attachment row,
	 * which is the other half of what made the build time out.
	 *
	 * @param array $ids Post IDs whose images are about to be read.
	 */
	protected static function prime_attachments( $ids ) {
		$attachments = array();

		foreach ( (array) $ids as $id ) {
			foreach ( array( 'program_banner_image', '_thumbnail_id' ) as $key ) {
				$value = get_post_meta( $id, $key, true );
				if ( $value && is_numeric( $value ) ) {
					$attachments[] = (int) $value;
				}
			}
		}

		$attachments = array_unique( array_filter( $attachments ) );

		if ( $attachments ) {
			_prime_post_caches( $attachments, false, true );
		}
	}

	/**
	 * Permalink with the site URL chopped off the front.
	 *
	 * @param int    $id   Post.
	 * @param string $home Home URL with trailing slash.
	 * @return string
	 */
	protected static function relative_link( $id, $home ) {
		$link = (string) get_permalink( $id );
		return 0 === strpos( $link, $home ) ? substr( $link, strlen( $home ) ) : $link;
	}

	/**
	 * Thumbnail with the uploads URL chopped off the front.
	 *
	 * @param int    $id Post.
	 * @param string $up Uploads URL with trailing slash.
	 * @return string
	 */
	protected static function relative_image( $id, $up ) {
		$image = '';

		// Raw meta rather than get_field(): ACF stores the attachment ID here, and
		// asking it for the formatted array rebuilds every size on every call.
		$banner = get_post_meta( $id, 'program_banner_image', true );

		if ( $banner && is_numeric( $banner ) ) {
			$image = wp_get_attachment_image_url( (int) $banner, 'medium' );
		}

		if ( ! $image ) {
			$image = get_the_post_thumbnail_url( $id, 'medium' );
		}

		if ( ! $image ) {
			return '';
		}

		return 0 === strpos( $image, $up ) ? substr( $image, strlen( $up ) ) : $image;
	}

	/**
	 * Write the index and return its filename.
	 *
	 * The content hash in the name is what lets the file be cached forever:
	 * a rebuild produces a new name, so nobody is ever served a stale index.
	 *
	 * @param array  $data Index.
	 * @param string $lang Language code.
	 * @return string|false Filename, or false on failure.
	 */
	protected static function write( $data, $lang ) {
		$dir = self::dir_path();

		if ( ! wp_mkdir_p( $dir ) ) {
			return false;
		}

		$flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

		// Encode row by row straight to disk. Encoding the whole index in one go
		// would hold the arrays and an equally large JSON string in memory at the
		// same time, which is the point a big site runs out.
		$temp   = trailingslashit( $dir ) . 'index-building-' . ( $lang ? $lang : 'x' ) . '.json';
		$handle = fopen( $temp, 'w' );

		if ( ! $handle ) {
			return false;
		}

		$header = array(
			'v'     => $data['v'],
			'built' => $data['built'],
			'lang'  => $data['lang'],
			'home'  => $data['home'],
			'up'    => $data['up'],
		);

		// Opening brace plus the scalar header, left unclosed so the lists can be
		// appended after it.
		fwrite( $handle, rtrim( wp_json_encode( $header, $flags ), '}' ) );

		foreach ( array( 'pr', 'pp', 'ar', 'se', 'pg' ) as $key ) {
			fwrite( $handle, ',"' . $key . '":[' );

			$first = true;
			foreach ( $data[ $key ] as $row ) {
				fwrite( $handle, ( $first ? '' : ',' ) . wp_json_encode( $row, $flags ) );
				$first = false;
			}

			fwrite( $handle, ']' );
		}

		fwrite( $handle, '}' );
		fclose( $handle );

		$name = 'index-' . ( $lang ? $lang . '-' : '' ) . substr( md5_file( $temp ), 0, 12 ) . '.json';
		$final = trailingslashit( $dir ) . $name;

		// Move into place only once the file is complete, so a build that dies
		// halfway cannot leave a truncated index being served.
		if ( ! rename( $temp, $final ) ) {
			@unlink( $temp );
			return false;
		}

		return $name;
	}

	/**
	 * Delete indexes that are no longer referenced.
	 *
	 * @param array $keep Filenames currently live.
	 */
	protected static function prune_old_files( $keep ) {
		$dir   = trailingslashit( self::dir_path() );
		$files = glob( $dir . 'index-*.json' );

		foreach ( (array) $files as $path ) {
			if ( ! in_array( basename( $path ), $keep, true ) ) {
				@unlink( $path );
			}
		}
	}

	/**
	 * @return string
	 */
	public static function dir_path() {
		return trailingslashit( wp_upload_dir()['basedir'] ) . self::DIR;
	}

	/**
	 * Public URL of the index for a language, or '' when it has not been built.
	 *
	 * @param string $lang Language code.
	 * @return string
	 */
	public static function url( $lang = null ) {
		if ( null === $lang ) {
			$lang = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '';
		}

		$files = get_option( self::OPTION_FILES, array() );

		if ( ! is_array( $files ) ) {
			return '';
		}

		if ( ! isset( $files[ $lang ] ) ) {
			// Single-language site, or a language that was added after the last build.
			$lang = key( $files );
			if ( null === $lang ) {
				return '';
			}
		}

		return trailingslashit( wp_upload_dir()['baseurl'] ) . self::DIR . '/' . $files[ $lang ];
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function report() {
		$report = get_option( self::OPTION_REPORT, array() );
		return is_array( $report ) ? $report : array();
	}
}
