<?php
/**
 * Sample Course Importer
 *
 * @package Tutor\Setup
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com.
 * @since   4.0.0
 */

namespace TUTOR;

use Tutor\Models\CourseModel;

defined( 'ABSPATH' ) || exit;

/**
 * Class SampleCourse
 *
 * Handles immediate, synchronous import of sample courses from a remote JSON file URL.
 */
class SampleCourse {

	/**
	 * Max number of course import.
	 *
	 * @var int
	 */
	private $max_course_import = 4;

	/**
	 * Map to store relations of old course/item IDs to new database IDs.
	 *
	 * @var array
	 */
	private $courses_map = array();

	/**
	 * Imports sample courses from a JSON file URL.
	 *
	 * Runs synchronously in a single request and is optimized for small-scale imports.
	 *
	 * @param string $json_file_url The URL of the sample courses JSON file.
	 *
	 * @return array|\WP_Error Array of newly created course IDs on success, or WP_Error on failure.
	 */
	public function import( $json_file_url ) {
		if ( empty( $json_file_url ) ) {
			return new \WP_Error( 'invalid_url', __( 'Invalid JSON file URL provided.', 'tutor' ) );
		}

		// Fetch raw content from S3 or external URL.
		$response = wp_safe_remote_get( $json_file_url, array( 'timeout' => 300 ) );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return new \WP_Error( 'empty_body', __( 'The JSON file is empty.', 'tutor' ) );
		}

		$contents = json_decode( $body, true );
		if ( ! is_array( $contents ) || ! isset( $contents['data'] ) ) {
			return new \WP_Error( 'invalid_json', __( 'Invalid sample course JSON structure.', 'tutor' ) );
		}

		// Determine if media files should be downloaded (defaults to true for sample courses)..
		$keep_media_files = isset( $contents['keep_media_files'] ) ? (bool) $contents['keep_media_files'] : true;

		$imported_course_ids = array();
		$this->courses_map   = array();

		// Raise memory limits and execution time for the single request process..
		if ( function_exists( 'wp_raise_memory_limit' ) ) {
			wp_raise_memory_limit( 'admin' );
		}

		@set_time_limit( 600 );

		// Iterate through the sections of the JSON file..
		foreach ( $contents['data'] as $section ) {
			$content_type = $section['content_type'] ?? '';
			$data_list    = $section['data'] ?? array();

			// Only import course data lists..
			if ( 'courses' !== $content_type && tutor()->course_post_type !== $content_type ) {
				continue;
			}

			if ( ! is_array( $data_list ) ) {
				continue;
			}

			$data_list = array_slice( $data_list, 0, $this->max_course_import );
			foreach ( $data_list as $course ) {
				$import_id        = $course['ID'] ?? null;
				$child_contents   = $course['contents'] ?? null;
				$taxonomies       = $course['taxonomies'] ?? null;
				$meta             = $course['meta'] ?? null;
				$thumbnail_url    = $course['thumbnail_url'] ?? null;
				$attachment_links = $course['attachment_links'] ?? null;
				$attachment_ids   = array();

				// Clean and prepare post data..
				$cleaned_course  = $this->unset_post_data( $course );
				$prepared_course = $this->prepare_post_data( $cleaned_course );

				if ( is_wp_error( $prepared_course ) ) {
					continue;
				}

				// Insert course post..
				$course_id = wp_insert_post( $prepared_course, true );
				if ( is_wp_error( $course_id ) ) {
					continue;
				}

				$imported_course_ids[] = $course_id;

				// Map the old course ID to the newly created course ID..
				if ( $import_id ) {
					$this->courses_map[ $import_id ] = array( 'course_id' => $course_id );
				}

				// Set course categories and tags..
				if ( $taxonomies ) {
					$this->set_course_taxonomies( $course_id, $taxonomies );
				}

				// Fetch and download attachments if requested..
				if ( $keep_media_files && $attachment_links ) {
					$attachment_ids = $this->get_attachment_ids_from_urls( $attachment_links );
				}

				// Import course metadata..
				if ( $meta ) {
					$this->import_post_meta( $course_id, $meta, $keep_media_files );
				}

				// Save attachment meta if attachments were processed..
				if ( ! empty( $attachment_ids ) ) {
					update_post_meta( $course_id, '_tutor_attachments', $attachment_ids );
				}

				// Save course thumbnail..
				if ( $keep_media_files && $thumbnail_url ) {
					$this->save_post_thumbnail( $thumbnail_url, $course_id );
				}

				// Recursively import course topics, lessons, quizzes, and assignments..
				if ( is_array( $child_contents ) && count( $child_contents ) ) {
					$this->import_course_topics( $child_contents, $course_id, $keep_media_files );
				}
			}
		}

		return $imported_course_ids;
	}

	/**
	 * Recursively imports course topics and their child contents.
	 *
	 * @param array $topics           List of topic content arrays.
	 * @param int   $course_id        The parent course post ID.
	 * @param bool  $keep_media_files Whether to keep media files.
	 *
	 * @return void
	 */
	private function import_course_topics( $topics, $course_id, $keep_media_files = false ) {
		foreach ( $topics as $topic ) {
			$post_type = $topic['post_type'] ?? '';

			if ( 'topics' !== $post_type && tutor()->topics_post_type !== $post_type ) {
				continue;
			}

			$children = $topic['children'] ?? array();

			// Clean and prepare topic post data..
			$cleaned_topic                 = $this->unset_post_data( $topic );
			$prepared_topic                = $this->prepare_post_data( $cleaned_topic );
			$prepared_topic['post_parent'] = $course_id;

			$topic_id = wp_insert_post( $prepared_topic, true );
			if ( is_wp_error( $topic_id ) ) {
				continue;
			}

			// Save topic metadata..
			$meta = $topic['meta'] ?? null;
			if ( $meta ) {
				$this->import_post_meta( $topic_id, $meta, $keep_media_files );
			}

			// Process children of this topic (lessons, quizzes, assignments)..
			if ( is_array( $children ) && count( $children ) ) {
				foreach ( $children as $child ) {
					$child_post_type = $child['post_type'] ?? '';

					if ( tutor()->lesson_post_type === $child_post_type ) {
						$this->import_lesson( $child, $topic_id, $keep_media_files );
					} elseif ( tutor()->quiz_post_type === $child_post_type ) {
						$this->import_quiz( $child, $topic_id, $keep_media_files );
					} elseif ( tutor()->assignment_post_type === $child_post_type ) {
						$this->import_assignment( $child, $topic_id, $keep_media_files );
					}
				}
			}
		}
	}

	/**
	 * Imports a lesson under a topic.
	 *
	 * @param array $lesson_data     Lesson data array.
	 * @param int   $parent_topic_id Parent topic ID.
	 * @param bool  $keep_media_files Whether to keep media files.
	 *
	 * @return int|\WP_Error The created lesson ID or WP_Error.
	 */
	private function import_lesson( $lesson_data, $parent_topic_id, $keep_media_files = false ) {
		$meta             = $lesson_data['meta'] ?? null;
		$thumbnail_url    = $lesson_data['thumbnail_url'] ?? null;
		$attachment_links = $lesson_data['attachment_links'] ?? null;
		$attachment_ids   = array();

		// Clean and prepare post data..
		$lesson_data                = $this->unset_post_data( $lesson_data );
		$lesson_data                = $this->prepare_post_data( $lesson_data );
		$lesson_data['post_parent'] = $parent_topic_id;

		$lesson_id = wp_insert_post( $lesson_data, true );
		if ( is_wp_error( $lesson_id ) ) {
			return $lesson_id;
		}

		// Download media attachments.
		if ( $keep_media_files && is_array( $attachment_links ) ) {
			$attachment_ids = $this->get_attachment_ids_from_urls( $attachment_links );
		}

		// Import lesson metadata.
		if ( $meta ) {
			$this->import_post_meta( $lesson_id, $meta, $keep_media_files );
		}

		if ( ! empty( $attachment_ids ) ) {
			update_post_meta( $lesson_id, '_tutor_attachments', $attachment_ids );
		}

		if ( $keep_media_files && $thumbnail_url ) {
			$this->save_post_thumbnail( $thumbnail_url, $lesson_id );
		}

		return $lesson_id;
	}

	/**
	 * Imports an assignment under a topic.
	 *
	 * @param array $assignment_data Assignment data array.
	 * @param int   $parent_topic_id Parent topic ID.
	 * @param bool  $keep_media_files Whether to keep media files.
	 *
	 * @return int|\WP_Error The created assignment ID or WP_Error.
	 */
	private function import_assignment( $assignment_data, $parent_topic_id, $keep_media_files = false ) {
		$meta             = $assignment_data['meta'] ?? null;
		$attachment_links = $assignment_data['attachment_links'] ?? null;
		$attachment_ids   = array();

		// Clean and prepare post data.
		$assignment_data                = $this->unset_post_data( $assignment_data );
		$assignment_data                = $this->prepare_post_data( $assignment_data );
		$assignment_data['post_parent'] = $parent_topic_id;

		$assignment_id = wp_insert_post( $assignment_data, true );
		if ( is_wp_error( $assignment_id ) ) {
			return $assignment_id;
		}

		// Download media attachments.
		if ( $keep_media_files && is_array( $attachment_links ) ) {
			$attachment_ids = $this->get_attachment_ids_from_urls( $attachment_links );
		}

		// Import assignment metadata.
		if ( $meta ) {
			$this->import_post_meta( $assignment_id, $meta, $keep_media_files );
		}

		if ( ! empty( $attachment_ids ) ) {
			update_post_meta( $assignment_id, '_tutor_assignment_attachments', $attachment_ids );
		}

		// Update parent course ID for assignments.
		$topic_id  = wp_get_post_parent_id( $assignment_id );
		$course_id = wp_get_post_parent_id( $topic_id );
		update_post_meta( $assignment_id, '_tutor_course_id_for_assignments', $course_id );

		return $assignment_id;
	}

	/**
	 * Imports a quiz under a topic.
	 *
	 * @param array $quiz_data       Quiz data array.
	 * @param int   $parent_topic_id Parent topic ID.
	 * @param bool  $keep_media_files Whether to keep media files.
	 *
	 * @return int|\WP_Error The created quiz ID or WP_Error.
	 */
	private function import_quiz( $quiz_data, $parent_topic_id, $keep_media_files = false ) {
		$meta            = $quiz_data['meta'] ?? null;
		$question_answer = $quiz_data['question_answer'] ?? null;

		// Clean and prepare post data.
		$quiz_data                = $this->unset_post_data( $quiz_data );
		$quiz_data                = $this->prepare_post_data( $quiz_data );
		$quiz_data['post_parent'] = $parent_topic_id;

		$quiz_id = wp_insert_post( $quiz_data, true );
		if ( is_wp_error( $quiz_id ) ) {
			return $quiz_id;
		}

		// Import quiz metadata.
		if ( $meta ) {
			$this->import_post_meta( $quiz_id, $meta, $keep_media_files );
		}

		// Parse and save quiz questions and answers.
		if ( is_array( $question_answer ) && count( $question_answer ) ) {
			$quiz_question_answer = $this->flatten_quiz_question_answer( array( $quiz_id => $question_answer ) );
			$this->save_quiz_questions_answers( $quiz_question_answer, $quiz_id );
		}

		return $quiz_id;
	}

	/**
	 * Flattens nested quiz questions and answers into a structured list.
	 *
	 * @param array $data Data containing quiz question/answers.
	 *
	 * @return array Structured array of questions and answers.
	 */
	private function flatten_quiz_question_answer( $data ) {
		$flatten_content = array(
			'question' => array(),
			'answers'  => array(),
		);

		if ( is_array( $data ) ) {
			foreach ( $data as $quiz_id => $question_answer ) {
				if ( ! is_array( $question_answer ) ) {
					continue;
				}
				foreach ( $question_answer as $qa ) {
					$question = $qa['question'] ?? null;
					$answers  = $qa['answers'] ?? null;

					if ( $question ) {
						$question['quiz_id']           = $quiz_id;
						$flatten_content['question'][] = $question;
					}

					if ( is_array( $answers ) ) {
						foreach ( $answers as $answer ) {
							$flatten_content['answers'][] = $answer;
						}
					}
				}
			}
		}

		return $flatten_content;
	}

	/**
	 * Saves quiz questions and answers directly to database tables.
	 *
	 * Uses direct `$wpdb->insert` to preserve mappings and retrieve correct autoincremented keys.
	 *
	 * @param array $quiz_questions_answers Flattened list of quiz questions and answers.
	 * @param int   $quiz_id                The target quiz post ID.
	 *
	 * @return void
	 */
	private function save_quiz_questions_answers( $quiz_questions_answers, $quiz_id ) {
		global $wpdb;

		$table_question = "{$wpdb->prefix}tutor_quiz_questions";
		$table_answer   = "{$wpdb->prefix}tutor_quiz_question_answers";

		$questions        = $quiz_questions_answers['question'] ?? array();
		$answers          = $quiz_questions_answers['answers'] ?? array();
		$question_ids_map = array();

		// 1. Insert Questions.
		foreach ( $questions as $q ) {
			$old_question_id = $q['question_id'] ?? 0;

			// Handle question type normalization.
			if ( isset( $q['question_type'] ) ) {
				if ( 'image_matching' === $q['question_type'] ) {
					$q['question_type'] = 'matching';
				}
				if ( 'single_choice' === $q['question_type'] ) {
					$q['question_type'] = 'multiple_choice';
				}
			}

			$q['question_title'] = isset( $q['question_title'] ) ? wp_unslash( $q['question_title'] ) : '';
			if ( isset( $q['question_description'] ) ) {
				$q['question_description'] = wp_unslash( $q['question_description'] );
			}
			if ( isset( $q['answer_explanation'] ) ) {
				$q['answer_explanation'] = wp_unslash( $q['answer_explanation'] );
			}

			// Clean settings.
			$settings = $q['question_settings'] ?? array();
			if ( is_string( $settings ) ) {
				$settings = maybe_unserialize( $settings );
			}
			if ( is_array( $settings ) ) {
				if ( isset( $settings['question_type'] ) ) {
					if ( 'single_choice' === $settings['question_type'] ) {
						$settings['question_type'] = 'multiple_choice';
					}
					if ( 'image_matching' === $settings['question_type'] ) {
						$settings['is_image_matching'] = true;
					}
				}
			}
			$q['question_settings'] = maybe_serialize( $settings );

			unset( $q['question_id'] );
			$q['quiz_id'] = $quiz_id;

			$inserted = $wpdb->insert( $table_question, $q );
			if ( false !== $inserted ) {
				$new_question_id = $wpdb->insert_id;
				if ( $old_question_id ) {
					$question_ids_map[ $old_question_id ] = $new_question_id;
				}
			}
		}

		// 2. Insert Answers.
		foreach ( $answers as $a ) {
			$old_belongs_to = $a['belongs_question_id'] ?? 0;

			// Assign the newly generated parent question ID.
			if ( isset( $question_ids_map[ $old_belongs_to ] ) ) {
				$a['belongs_question_id'] = $question_ids_map[ $old_belongs_to ];
			} else {
				continue;
			}

			if ( isset( $a['belongs_question_type'] ) ) {
				if ( 'single_choice' === $a['belongs_question_type'] ) {
					$a['belongs_question_type'] = 'multiple_choice';
				}
				if ( 'image_matching' === $a['belongs_question_type'] ) {
					$a['belongs_question_type'] = 'matching';
				}
			}

			if ( isset( $a['answer_title'] ) ) {
				$a['answer_title'] = wp_unslash( $a['answer_title'] );
			}

			// Process remote answer image if applicable.
			if ( ! empty( $a['image_url'] ) ) {
				$upload_data = $this->upload_file_by_url( $a['image_url'] );
				if ( ! is_wp_error( $upload_data ) ) {
					$a['image_id'] = $upload_data['id'];
				}
			}

			unset( $a['image_url'] );
			unset( $a['answer_id'] );

			$wpdb->insert( $table_answer, $a );
		}
	}

	/**
	 * Sanitizes and prepares post data fields.
	 *
	 * @param array $post Raw post data array.
	 *
	 * @return array Sanitize post array.
	 */
	private function prepare_post_data( $post ) {
		$post                = sanitize_post( $post, 'db' );
		$post['post_author'] = get_current_user_id() ? get_current_user_id() : 1;
		return $post;
	}

	/**
	 * Removes fields that are not part of standard post tables.
	 *
	 * @param array $post_data Post data containing export tags.
	 *
	 * @return array Stripped post data.
	 */
	private function unset_post_data( $post_data ) {
		$keys         = array( 'ID', 'filter', 'meta', 'thumbnail_url', 'child_posts', 'attachment_links', 'question', 'answers', 'courses', 'contents' );
		$updated_data = $post_data;

		foreach ( $keys as $key ) {
			if ( isset( $updated_data[ $key ] ) ) {
				unset( $updated_data[ $key ] );
			}
		}

		return $updated_data;
	}

	/**
	 * Downloads and saves categories and tags for the course.
	 *
	 * @param int   $course_id  Newly created course ID.
	 * @param array $taxonomies Categories and tags arrays.
	 *
	 * @return void
	 */
	private function set_course_taxonomies( $course_id, $taxonomies ) {
		$categories   = $taxonomies['categories'] ?? array();
		$tags         = $taxonomies['tags'] ?? array();
		$cat_taxonomy = class_exists( 'Tutor\Models\CourseModel' ) ? CourseModel::COURSE_CATEGORY : 'course-category';
		$tag_taxonomy = class_exists( 'Tutor\Models\CourseModel' ) ? CourseModel::COURSE_TAG : 'course-tag';

		// Set categories.
		if ( ! empty( $categories ) && is_array( $categories ) ) {
			$term_ids = array();
			foreach ( $categories as $category ) {
				$term = term_exists( $category['name'], $cat_taxonomy );
				if ( ! $term ) {
					$parent_id = 0;
					if ( ! empty( $category['parent'] ) ) {
						$parent_name = '';
						foreach ( $categories as $c ) {
							if ( (int) $c['term_id'] === (int) $category['parent'] ) {
								$parent_name = $c['name'];
								break;
							}
						}
						if ( $parent_name ) {
							$parent_term = get_term_by( 'name', $parent_name, $cat_taxonomy );
							if ( $parent_term ) {
								$parent_id = $parent_term->term_id;
							}
						}
					}

					$inserted = wp_insert_term(
						$category['name'],
						$cat_taxonomy,
						array(
							'parent'      => $parent_id,
							'description' => $category['description'] ?? '',
							'slug'        => $category['slug'] ?? '',
						)
					);
					if ( ! is_wp_error( $inserted ) ) {
						$term_ids[] = (int) $inserted['term_id'];
					}
				} else {
					$term_ids[] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
				}
			}
			wp_set_object_terms( $course_id, $term_ids, $cat_taxonomy );
		}

		// Set tags.
		if ( ! empty( $tags ) && is_array( $tags ) ) {
			$term_ids = array();
			foreach ( $tags as $tag ) {
				$term = term_exists( $tag['name'], $tag_taxonomy );
				if ( ! $term ) {
					$inserted = wp_insert_term(
						$tag['name'],
						$tag_taxonomy,
						array(
							'description' => $tag['description'] ?? '',
							'slug'        => $tag['slug'] ?? '',
						)
					);
					if ( ! is_wp_error( $inserted ) ) {
						$term_ids[] = (int) $inserted['term_id'];
					}
				} else {
					$term_ids[] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
				}
			}
			wp_set_object_terms( $course_id, $term_ids, $tag_taxonomy );
		}
	}

	/**
	 * Imports metadata for any custom post.
	 *
	 * @param int   $post_id          Post ID.
	 * @param array $post_meta        Associative array of metadata keys and values.
	 * @param bool  $keep_media_files Whether to keep media files.
	 *
	 * @return void
	 */
	private function import_post_meta( $post_id, $post_meta, $keep_media_files = false ) {
		if ( ! is_array( $post_meta ) || ! count( $post_meta ) ) {
			return;
		}

		$normalized_meta = array();
		foreach ( $post_meta as $key => $values ) {
			$normalized_meta[ $key ] = is_array( $values ) ? ( isset( $values[0] ) ? $values[0] : null ) : $values;
		}

		unset( $normalized_meta['_thumbnail_id'] );
		unset( $normalized_meta['_tutor_attachments'] );
		unset( $normalized_meta['_tutor_assignment_attachments'] );
		unset( $normalized_meta['_tutorstarter_schema'] );

		foreach ( $normalized_meta as $key => $value ) {
			if ( '_video' === $key ) {
				if ( is_array( $value ) && isset( $value['source'] ) && 'html5' === $value['source'] && $keep_media_files ) {
					$video_url   = $value['source_html5'] ?? '';
					$upload_data = $this->upload_file_by_url( $video_url );
					if ( ! is_wp_error( $upload_data ) ) {
						$value['source_video_id'] = $upload_data['id'];
						$value['source_html5']    = $upload_data['url'];
					}
				}
			}

			if ( '_tutor_course_id_for_assignments' === $key ) {
				if ( isset( $this->courses_map[ $value ] ) ) {
					$value = $this->courses_map[ $value ]['course_id'];
				}
			}

			update_post_meta( $post_id, $key, $value );
		}
	}

	/**
	 * Helper function to download and register files from remote URL.
	 *
	 * @param string $file_url Remote URL of the file.
	 *
	 * @return array|\WP_Error Meta of the newly created attachment, or WP_Error on failure.
	 */
	private function upload_file_by_url( $file_url ) {
		if ( empty( $file_url ) ) {
			return new \WP_Error( 'invalid_file_url', 'Invalid file URL provided.' );
		}

		$upload_dir_info = wp_upload_dir();
		$upload_dir      = $upload_dir_info['basedir'];

		$parse_url = parse_url( $file_url );
		$base_url  = ( $parse_url['scheme'] ?? 'http' ) . '://' . ( $parse_url['host'] ?? '' );

		if ( isset( $parse_url['port'] ) ) {
			$base_url .= ':' . $parse_url['port'];
		}

		if ( isset( $parse_url['path'] ) ) {
			$base_url .= strstr( $parse_url['path'], 'wp-content', true );
		}

		$file_name       = basename( $file_url );
		$source_dir_url  = str_replace( $file_name, '', $file_url );
		$source_dir_part = str_replace( $base_url . 'wp-content/uploads/', '', $source_dir_url );

		$file_path  = trailingslashit( $upload_dir ) . trailingslashit( $source_dir_part ) . $file_name;
		$target_dir = trailingslashit( $upload_dir ) . trailingslashit( $source_dir_part );

		try {
			if ( ! file_exists( $file_path ) ) {
				if ( ! file_exists( $target_dir ) ) {
					wp_mkdir_p( $target_dir );
				}

				$response  = wp_safe_remote_get( $file_url, array( 'timeout' => 300 ) );
				$file_data = wp_remote_retrieve_body( $response );

				if ( ! empty( $file_data ) ) {
					file_put_contents( $file_path, $file_data );
				} else {
					return new \WP_Error( 'download_failed', 'Failed to download content ' . $file_url );
				}
			}
		} catch ( \Throwable $th ) {
			return new \WP_Error( 'download_failed', 'Failed to download content ' . $file_url . ': ' . $th->getMessage() );
		}

		$file_type = wp_check_filetype( $file_name );

		$final_file_url = str_replace( $source_dir_url, site_url( '/wp-content/uploads/' . $source_dir_part ), $file_url );

		$attachment_args = array(
			'guid'           => $final_file_url,
			'post_mime_type' => $file_type['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name ),
			'post_status'    => 'inherit',
		);

		$attach_id = wp_insert_attachment( $attachment_args, $file_path, 0, true );

		if ( is_wp_error( $attach_id ) ) {
			return $attach_id;
		}

		if ( wp_attachment_is_image( $attach_id ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
			wp_update_attachment_metadata( $attach_id, $attach_data );
		}

		return array(
			'url'         => $final_file_url,
			'id'          => $attach_id,
			'name'        => $file_name,
			'upload_path' => trailingslashit( $source_dir_part ) . $file_name,
			'type'        => $file_type,
		);
	}

	/**
	 * Sets the thumbnail of a post.
	 *
	 * @param string $thumbnail_url Thumbnail remote URL.
	 * @param int    $post_id       Post ID.
	 *
	 * @return bool
	 */
	private function save_post_thumbnail( $thumbnail_url, $post_id ) {
		$upload_data = $this->upload_file_by_url( $thumbnail_url );
		if ( ! is_wp_error( $upload_data ) ) {
			return (bool) set_post_thumbnail( $post_id, $upload_data['id'] );
		}
		return false;
	}

	/**
	 * Helper function to retrieve multiple attachment IDs from URLs.
	 *
	 * @param array $attachment_urls List of remote attachment URLs.
	 *
	 * @return array List of local attachment IDs.
	 */
	private function get_attachment_ids_from_urls( array $attachment_urls ) {
		$attachment_ids = array();
		foreach ( $attachment_urls as $url ) {
			if ( $url ) {
				$upload_data = $this->upload_file_by_url( $url );
				if ( ! is_wp_error( $upload_data ) ) {
					$attachment_ids[] = $upload_data['id'];
				}
			}
		}
		return $attachment_ids;
	}
}
