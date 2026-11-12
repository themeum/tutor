<?php
/**
 * Tutor pages
 *
 * @package Tutor\Views
 * @subpackage Tutor\Tools
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.1
 */

use Tutor\Core\DB;
use TUTOR\RestAuth;

$user_id = get_current_user_id();

// Getting user meta using custom query since get_user_meta .
// not return umeta_id .
$tokens = DB::table( 'usermeta' )
	->where( 'user_id', $user_id )
	->where( 'meta_key', RestAuth::KEYS_USER_META_KEY )
	->order_by( 'umeta_id', 'DESC' )
	->get();

$permissions          = RestAuth::available_permissions();
$user                 = get_userdata( get_current_user_id() );
$access_days          = RestAuth::ttl_seconds_to_days( RestAuth::get_access_ttl() );
$refresh_days         = RestAuth::ttl_seconds_to_days( RestAuth::get_refresh_ttl() );
$default_access_days  = RestAuth::ttl_seconds_to_days( RestAuth::ACCESS_TTL );
$default_refresh_days = RestAuth::ttl_seconds_to_days( RestAuth::REFRESH_TTL );

?>
<div class="tutor-option-main-title">
	<div class="tutor-fs-4 tutor-fw-medium tutor-color-black">
		<?php esc_html_e( 'REST API', 'tutor' ); ?>
	</div>
</div>

<div class="tutor-option-single-item tutor-mb-32">
	<div class="tutor-option-group-title tutor-mb-16">
		<div class="tutor-fs-6 tutor-color-muted">
			<?php esc_html_e( 'Token Lifetime', 'tutor' ); ?>
		</div>
	</div>
	<form id="tutor-rest-api-token-settings" class="item-wrapper" method="post" autocomplete="off">
		<?php tutor_nonce_field(); ?>
		<input type="hidden" name="action" value="tutor_save_rest_api_token_settings">

		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<div class="tutor-fs-6 tutor-fw-medium">
					<?php esc_html_e( 'Access Token Lifetime', 'tutor' ); ?>
				</div>
				<div class="tutor-fs-7 tutor-color-muted tutor-mt-8">
					<?php
					printf(
						/* translators: 1: min days, 2: max days */
						esc_html__( 'Set how many days an access token stays valid. Allowed range: %1$d to %2$d. Enter 0 for no expiration', 'tutor' ),
						(int) RestAuth::MIN_TTL_DAYS,
						(int) RestAuth::MAX_TTL_DAYS
					);
					?>
				</div>
			</div>
			<div class="tutor-option-field-input">
				<input
					class="tutor-form-control tutor-w-160"
					type="number"
					name="<?php echo esc_attr( RestAuth::OPTION_ACCESS_TTL ); ?>"
					value="<?php echo esc_attr( (string) $access_days ); ?>"
					min="0"
					max="<?php echo esc_attr( (string) RestAuth::MAX_TTL_DAYS ); ?>"
					step="1"
					required
				>
			</div>
		</div>

		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<div class="tutor-fs-6 tutor-fw-medium">
					<?php esc_html_e( 'Refresh Token Lifetime', 'tutor' ); ?>
				</div>
				<div class="tutor-fs-7 tutor-color-muted tutor-mt-8">
					<?php
					printf(
						/* translators: 1: min days, 2: max days */
						esc_html__( 'Set how many days a refresh token stays valid. Allowed range: %1$d to %2$d. Enter 0 for no expiration. It must be longer than the Access Token Lifetime.', 'tutor' ),
						(int) RestAuth::MIN_TTL_DAYS,
						(int) RestAuth::MAX_TTL_DAYS
					);
					?>
				</div>
			</div>
			<div class="tutor-option-field-input">
				<input
					class="tutor-form-control tutor-w-160"
					type="number"
					name="<?php echo esc_attr( RestAuth::OPTION_REFRESH_TTL ); ?>"
					value="<?php echo esc_attr( (string) $refresh_days ); ?>"
					min="0"
					max="<?php echo esc_attr( (string) RestAuth::MAX_TTL_DAYS ); ?>"
					step="1"
					required
				>
			</div>
		</div>

		<div class="tutor-fs-7 tutor-color-warning tutor-mt-12 tutor-mb-16">
			<?php esc_html_e( 'Note: It\'s not recommended to set the token lifetime to 0 (no expiration). A compromised token would remain valid indefinitely and increase security risk.', 'tutor' ); ?>
		</div>

		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label"></div>
			<div class="tutor-option-field-input">
				<button type="submit" class="tutor-btn tutor-btn-primary">
					<?php esc_html_e( 'Save Changes', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</form>
</div>

<div class="tutor-rest-api-keys-wrapper">
	<button class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-mb-12" data-tutor-modal-target="tutor-add-new-api-keys">
		+ <?php esc_html_e( 'Add New', 'tutor' ); ?>
	</button>

	<table class="tutor-table tutor-pages-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'User', 'tutor' ); ?></th>
				<th><?php esc_html_e( 'API Key', 'tutor' ); ?></th>
				<th><?php esc_html_e( 'Permission', 'tutor' ); ?></th>
				<th><?php esc_html_e( 'Action', 'tutor' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if ( is_array( $tokens ) && count( $tokens ) ) {
				foreach ( $tokens as $token ) {
					$api = json_decode( $token->meta_value );
					if ( ! is_object( $api ) || empty( $api->key ) ) {
						continue;
					}
					// Migrate legacy plaintext secrets to HMAC; never pass secret to the UI.
					$api = RestAuth::persist_normalized_api_key_meta( (int) $token->umeta_id, $api );
					echo RestAuth::prepare_response( $token->umeta_id, $api->key, $api->permission ?? '', $api->description ?? '' ); //phpcs:ignore
				}
			} else {
				?>
				<tr>
					<td colspan="100%" id="tutor-api-keys-no-record">
					<?php esc_html_e( 'No record available', 'tutor' ); ?>
					</td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
</div>

<!-- add new token modal  -->
<div id="tutor-add-new-api-keys" class="tutor-modal tutor-modal-scrollable" role="dialog" aria-modal="true" aria-labelledby="tutor-add-new-api-keys-title" aria-hidden="true">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<form id="tutor-generate-api-keys" class="tutor-modal-content" autocomplete="off" method="post">
			<div class="tutor-modal-header">
				<div id="tutor-add-new-api-keys-title" class="tutor-modal-title">
					<?php esc_html_e( 'Generate API Key, Secret', 'tutor' ); ?>
				</div>
				<button type="button" class="tutor-iconic-btn tutor-modal-close" data-tutor-modal-close aria-label="<?php esc_attr_e( 'Close', 'tutor' ); ?>">
					<span class="tutor-icon-times" aria-hidden="true"></span>
				</button>
			</div>

			<div class="tutor-modal-body">
				<?php tutor_nonce_field(); ?>
				<input type="hidden" name="action" value="tutor_generate_api_keys">

				<div id="tutor-generate-api-keys-fields">
					<div class="tutor-row">
						<div class="tutor-col">
							<label class="tutor-form-label">
								<?php esc_html_e( 'User', 'tutor' ); ?>
							</label>
							<div class="tutor-mb-16">
								<input type="text" class="tutor-form-control" value="<?php echo esc_html( tutor_utils()->display_name( $user->ID ) ); ?>" disabled>
							</div>
						</div>
					</div>
					<div class="tutor-row">
						<div class="tutor-col">
							<label class="tutor-form-label" for="permission">
								<?php esc_html_e( 'Permission', 'tutor' ); ?>
							</label>
							<div class="tutor-mb-16">
								<select name="permission" id="permission" class="tutor-form-control" style="max-width: 100%;">
									<?php foreach ( $permissions as $permission ) : ?>
									<option value="<?php echo esc_attr( $permission['value'] ); ?>">
										<?php echo esc_html( $permission['label'] ); ?>
									</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="tutor-mb-16">
								<label class="tutor-form-label" for="description">
									<?php esc_html_e( 'Description', 'tutor' ); ?>
								</label>
								<textarea name="description" id="description" class="tutor-form-control" cols="30" rows="3" placeholder="<?php esc_html_e( 'Write here...', 'tutor' ); ?>"></textarea>
							</div>
							<div class="tutor-fs-7 tutor-color-warning">
								<?php esc_html_e( 'The API Secret will be shown only once. Copy it now and store it in a secure location. It cannot be retrieved later.', 'tutor' ); ?>
							</div>
						</div>
					</div>
				</div>

				<div id="tutor-generated-credentials" class="tutor-d-none">
					<div class="tutor-fs-7 tutor-color-warning tutor-mb-16">
						<?php esc_html_e( 'Make sure to copy both your API key and Secret now. The secret cannot be retrieved later.', 'tutor' ); ?>
					</div>
					<div class="tutor-mb-16">
						<label class="tutor-form-label" for="tutor-generated-api-key">
							<?php esc_html_e( 'API Key', 'tutor' ); ?>
						</label>
						<div class="tutor-d-flex tutor-align-center tutor-gap-1">
							<input
								type="text"
								id="tutor-generated-api-key"
								class="tutor-form-control"
								readonly
								value=""
							>
							<button type="button" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-flex-shrink-0" data-tutor-clipboard-copy-target="tutor-generated-api-key">
								<span class="tutor-icon-copy tutor-mr-8"></span>
								<?php esc_html_e( 'Copy', 'tutor' ); ?>
							</button>
						</div>
					</div>
					<div class="tutor-mb-8">
						<label class="tutor-form-label" for="tutor-generated-api-secret">
							<?php esc_html_e( 'Secret', 'tutor' ); ?>
						</label>
						<div class="tutor-d-flex tutor-align-center tutor-gap-1">
							<input
								type="text"
								id="tutor-generated-api-secret"
								class="tutor-form-control"
								readonly
								value=""
							>
							<button type="button" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-flex-shrink-0" data-tutor-clipboard-copy-target="tutor-generated-api-secret">
								<span class="tutor-icon-copy tutor-mr-8"></span>
								<?php esc_html_e( 'Copy', 'tutor' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>

			<div class="tutor-modal-footer">
				<button type="button" class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close id="tutor-generate-api-keys-cancel">
					<?php esc_html_e( 'Cancel', 'tutor' ); ?>
				</button>

				<button type="submit" class="tutor-btn tutor-btn-primary" id="tutor-generate-api-keys-submit">
					<?php esc_html_e( 'Generate', 'tutor' ); ?>
				</button>

				<button type="button" class="tutor-btn tutor-btn-primary tutor-d-none" data-tutor-modal-close id="tutor-generate-api-keys-done">
					<?php esc_html_e( 'Done', 'tutor' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>

<!-- Update permission modal  -->
<div id="tutor-update-permission-modal" class="tutor-modal tutor-modal-scrollable" role="dialog" aria-modal="true" aria-labelledby="tutor-update-permission-modal-title" aria-hidden="true">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<form id="tutor-update-permission-form" class="tutor-modal-content" autocomplete="off" method="post">
			<div class="tutor-modal-header">
				<div id="tutor-update-permission-modal-title" class="tutor-modal-title">
					<?php esc_html_e( 'Update API', 'tutor' ); ?>
				</div>
				<button type="button" class="tutor-iconic-btn tutor-modal-close" data-tutor-modal-close aria-label="<?php esc_attr_e( 'Close', 'tutor' ); ?>">
					<span class="tutor-icon-times" aria-hidden="true"></span>
				</button>
			</div>

			<div class="tutor-modal-body">
				<?php tutor_nonce_field(); ?>
				<input type="hidden" name="action" value="tutor_update_api_permission">
				<input type="hidden" name="meta_id">
				<div class="tutor-row">
					<div class="tutor-col">
						<label class="tutor-form-label">
							<?php esc_html_e( 'User', 'tutor' ); ?>
						</label>
						<div class="tutor-mb-16">
							<input type="text" class="tutor-form-control" value="<?php echo esc_html( tutor_utils()->display_name( $user->ID ) ); ?>" disabled>
						</div>
					</div>
				</div>
				<div class="tutor-row">
					<div class="tutor-col">
						<label class="tutor-form-label" for="permission">
							<?php esc_html_e( 'Permission', 'tutor' ); ?>
						</label>
						<div class="tutor-mb-16">
							<select name="permission" id="permission" class="tutor-form-control" style="max-width: 100%;">
								<?php foreach ( $permissions as $permission ) : ?>
								<option value="<?php echo esc_attr( $permission['value'] ); ?>">
									<?php echo esc_html( $permission['label'] ); ?>
								</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="tutor-mb-16">
							<label class="tutor-form-label" for="description">
								<?php esc_html_e( 'Description', 'tutor' ); ?>
							</label>
							<textarea name="description" id="description" class="tutor-form-control" cols="30" rows="3" placeholder="<?php esc_html_e( 'Write here...', 'tutor' ); ?>"></textarea>
						</div>
					</div>
				</div>
			</div>

			<div class="tutor-modal-footer">
				<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
					<?php esc_html_e( 'Cancel', 'tutor' ); ?>
				</button>

				<button type="submit" class="tutor-btn tutor-btn-primary">
					<?php esc_html_e( 'Submit', 'tutor' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>
