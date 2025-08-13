<?php
/**
 * Template for displaying gift course card
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.8.0
 */

if ( ! defined( 'TUTOR_PRO_VERSION' ) ) {
	return;
}
?>

<div class="tutor-gift-course-card tutor-d-flex tutor-align-center tutor-justify-between tutor-position-relative tutor-overflow-hidden">
	<!-- Left decorative element -->
	<div class="tutor-gift-card-decoration-left tutor-position-absolute">
		<svg width="32" height="12" viewBox="0 0 32 12" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M0 0.0195312H31.583L30.0012 3.00953L31.583 5.99953L30.0012 8.98953L31.583 11.9795H0V0.0195312Z" fill="#FE621E"/>
		</svg>
	</div>

	<!-- Gift box icon -->
	<div class="tutor-gift-box-icon tutor-position-relative">
		<svg class="tutor-mt-12" width="77" height="90" viewBox="0 0 77 94" fill="none" xmlns="http://www.w3.org/2000/svg">
			<rect x="15.853" y="30.8945" width="30.969" height="39.4215" rx="1.59423" transform="rotate(-8.27728 15.853 30.8945)" fill="#FFB640"/>
			<path d="M2.75119 22.497C2.58744 21.7058 3.09613 20.9316 3.88738 20.7678L63.3138 8.46964C64.105 8.30589 64.8792 8.81458 65.0429 9.60583L67.4304 21.1426C67.5942 21.9339 67.0855 22.7081 66.2943 22.8718L6.86788 35.17C6.07663 35.3337 5.30246 34.8251 5.13871 34.0338L2.75119 22.497Z" fill="#6DA4F3"/>
			<path d="M12.3213 17.902C14.3941 17.9857 18.5746 18.1503 18.7145 18.1381L32.2557 15.1377C33.7125 14.6682 37.0179 13.372 38.585 11.9433C40.5439 10.1575 41.5301 3.61081 36.3951 2.6485C32.2871 1.87866 29.8676 5.70354 29.1714 7.71221L27.0799 12.3582C26.1356 11.2951 25.5477 11.9631 24.0511 11.976C22.8539 11.9863 21.9523 12.9419 21.6512 13.4185C21.4041 13.391 20.4594 12.8175 18.6576 10.7425C16.8558 8.66758 15.2183 7.78232 14.6248 7.59907C10.1233 6.08821 8.16045 9.13134 7.74169 10.8418C6.66077 15.9636 10.3444 17.6827 12.3213 17.902Z" fill="#3E64DE"/>
			<rect x="21.7871" y="17.0625" width="6.69677" height="14.7073" transform="rotate(-11.6922 21.7871 17.0625)" fill="#3E64DE"/>
			<path opacity="0.06" d="M16.9994 38.5567L43.4309 27.4766L16.1196 33.2699L16.9994 38.5567Z" fill="black"/>
			<g filter="url(#filter0_d_16494_201654)">
				<rect x="37.6033" y="22.5078" width="31.5802" height="39.4215" rx="1.59423" transform="rotate(10.3062 37.6033 22.5078)" fill="#FFCC40"/>
			</g>
			<path opacity="0.06" d="M67.8905 31.9218L35.2495 35.833L67.1114 36.4132L67.8905 31.9218Z" fill="black"/>
			<path d="M4.59692 38.0433C4.59692 36.4272 5.90697 35.1172 7.523 35.1172H65.0697C66.6857 35.1172 67.9958 36.4272 67.9958 38.0433V78.5286C67.9958 80.1446 66.6858 81.4546 65.0697 81.4546H7.523C5.90698 81.4546 4.59692 80.1446 4.59692 78.5286V38.0433Z" fill="#6DA4F3"/>
			<rect x="24.2605" y="35.1172" width="6.65269" height="46.3374" fill="#3E64DE"/>
			<defs>
				<filter id="filter0_d_16494_201654" x="1.5821" y="12.1375" width="74.8038" height="81.1166" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
				<feFlood flood-opacity="0" result="BackgroundImageFix"/>
				<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
				<feOffset dx="-10.6282" dy="7.97113"/>
				<feGaussianBlur stdDeviation="9.29965"/>
				<feComposite in2="hardAlpha" operator="out"/>
				<feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.19 0"/>
				<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_16494_201654"/>
				<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_16494_201654" result="shape"/>
				</filter>
			</defs>
		</svg>
		<svg class="tutor-gift-box-icon-star tutor-gift-box-icon-star-2"  width="9" height="8" viewBox="0 0 9 8" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M5.79358 0.31253C6.12997 -0.0483064 6.73371 0.204065 6.71324 0.696952L6.61739 3.00392C6.61095 3.15888 6.67253 3.30892 6.78598 3.41468L8.47487 4.98913C8.83571 5.32552 8.58334 5.92926 8.09045 5.90878L5.78349 5.81293C5.62852 5.80649 5.47848 5.86808 5.37272 5.98152L3.79827 7.67042C3.46189 8.03126 2.85814 7.77888 2.87862 7.286L2.97447 4.97903C2.98091 4.82407 2.91933 4.67403 2.80588 4.56827L1.11698 2.99382C0.756147 2.65743 1.00852 2.05369 1.50141 2.07417L3.80837 2.17002C3.96333 2.17645 4.11337 2.11487 4.21913 2.00143L5.79358 0.31253Z" fill="#FE9978"/>
		</svg>
		<svg class="tutor-gift-box-icon-star tutor-gift-box-icon-star-1" width="8" height="7" viewBox="0 0 8 7" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M1.72237 1.14393C1.64885 0.438211 2.49296 0.0218865 3.00814 0.509765L4.11309 1.55614C4.27506 1.70953 4.49596 1.7845 4.71783 1.76138L6.23142 1.60371C6.93714 1.53019 7.35346 2.3743 6.86558 2.88948L5.81921 3.99443C5.66582 4.1564 5.59085 4.3773 5.61397 4.59918L5.77164 6.11276C5.84515 6.81848 5.00105 7.2348 4.48587 6.74692L3.38092 5.70055C3.21895 5.54716 2.99805 5.47219 2.77617 5.49531L1.26259 5.65298C0.556871 5.72649 0.140546 4.88239 0.628425 4.36721L1.6748 3.26226C1.82819 3.10029 1.90316 2.87939 1.88004 2.65751L1.72237 1.14393Z" fill="#FE621E"/>
		</svg>
	</div><!--/ tutor-gift-box-icon -->

	<!-- Main content -->
	<div class="tutor-gift-card-content tutor-position-relative">
		<h3 class="tutor-gift-card-title tutor-fs-4 tutor-fw-medium tutor-color-primary tutor-m-0"><?php esc_html_e( 'Congratulations!', 'tutor' ); ?></h3>
		<p class="tutor-gift-card-message tutor-fs-6 tutor-m-0">
			<?php esc_html_e( 'You have received a Gift from', 'tutor' ); ?> <span class="tutor-fw-bold"><?php esc_html_e( 'Joe Nevarro', 'tutor' ); ?></span>
		</p>
		<button class="tutor-gift-card-button tutor-btn tutor-btn-sm tutor-btn-primary tutor-btn-md tutor-mt-16" data-tutor-modal-target="tutor-greetings-popup">
			<?php esc_html_e( 'Open it Up', 'tutor' ); ?>
		</button>
	</div>

	<!-- Right decorative element -->
	<div class="tutor-gift-card-decoration-right tutor-d-none tutor-d-lg-block tutor-position-absolute">
		<svg class="tutor-d-block" width="103" height="152" viewBox="0 0 103 152" fill="none" xmlns="http://www.w3.org/2000/svg">
			<rect x="29.0985" width="11.358" height="152" fill="#FE621E"/>
			<rect x="25.8698" y="70.0195" width="77.1302" height="11.9571" fill="#FE621E"/>
			<rect x="60.7997" y="70.0195" width="12.6723" height="11.8622" fill="#C93E01"/>
			<rect x="28.8004" y="69.8164" width="11.9558" height="13.0825" fill="#C93E01"/>
			<path d="M7.14981 59.8209L28.8 69.818V82.9006L6.70732 92.4471C5.32642 93.0438 3.7344 92.8779 2.50617 92.0093C1.0057 90.9483 0.113647 89.2249 0.113647 87.3872V65.0934C0.113647 63.3204 0.892971 61.637 2.24486 60.4898C3.61028 59.3311 5.524 59.0701 7.14981 59.8209Z" fill="#FE621E"/>
			<path d="M2.50617 92.0071L2.17909 91.7758C0.883749 90.8599 0.113647 89.3721 0.113647 87.7856C0.113647 85.0865 2.30167 82.8985 5.00074 82.8985L28.8 82.8984L6.70732 92.4448C5.32642 93.0415 3.7344 92.8757 2.50617 92.0071Z" fill="#C93E01"/>
			<path d="M62.406 59.8209L40.7557 69.818V82.9006L62.8485 92.4471C64.2294 93.0438 65.8214 92.8779 67.0496 92.0093C68.5501 90.9483 69.4421 89.2249 69.4421 87.3872V65.0934C69.4421 63.3204 68.6628 61.637 67.3109 60.4898C65.9455 59.3311 64.0318 59.0701 62.406 59.8209Z" fill="#FE621E"/>
			<path d="M67.0496 92.0071L67.3767 91.7758C68.672 90.8599 69.4421 89.3721 69.4421 87.7856C69.4421 85.0865 67.2541 82.8985 64.555 82.8985L40.7557 82.8984L62.8485 92.4448C64.2294 93.0415 65.8214 92.8757 67.0496 92.0071Z" fill="#C93E01"/>
		</svg>
	</div>
</div>

<div class="tutor-modal" id="tutor-greetings-popup">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content tutor-modal-content-white">
			<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
				<span class="tutor-icon-times" area-hidden="true"></span>
			</button>

			<div class="tutor-greetings-modal">
				<div class="tutor-greetings-card-wrapper">
					<div class="tutor-overflow-hidden">
						<div class="tutor-greetings-card">
							<span class="tutor-greetings-circle tutor-greetings-circle-top-left"></span>
							<span class="tutor-greetings-circle tutor-greetings-circle-top-right"></span>
							<span class="tutor-greetings-circle tutor-greetings-circle-bottom-left"></span>
							<span class="tutor-greetings-circle tutor-greetings-circle-bottom-right"></span>

							<div class="tutor-greeting-to-person tutor-fs-6">
								<p class="tutor-m-0"><?php esc_html_e( 'Hey', 'tutor' ); ?> <span class="tutor-fw-medium"><?php esc_html_e( 'Jilon Mask', 'tutor' ); ?> </span>!</p>
							</div>
							<div class="tutor-greetings-content">
								<svg class="tutor-greetings-ribbon" width="68" height="57" viewBox="0 0 68 57" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M10.687 2.50712C29.1471 -2.63179 35.0839 18.8055 34.3473 28.2032H25.377C9.04883 28.2032 4.36022 20.666 4.05693 16.8974C3.12014 7.85272 8.08666 3.53526 10.687 2.50712Z" stroke="#FE621E" stroke-width="3"/>
									<path d="M58.1284 2.50712C39.6684 -2.63179 33.7315 18.8055 34.4682 28.2032H43.4384C59.7666 28.2032 64.4552 20.666 64.7585 16.8974C65.6953 7.85272 60.7288 3.53526 58.1284 2.50712Z" stroke="#FE621E" stroke-width="3"/>
									<path d="M34.4303 28.0586C30.9093 34.277 19.5745 48.3386 2.40381 54.8381" stroke="#FE621E" stroke-width="3" stroke-linecap="round"/>
									<path d="M34.4085 28.0586C37.9296 34.277 49.2644 48.3386 66.4351 54.8381" stroke="#FE621E" stroke-width="3" stroke-linecap="round"/>
								</svg>
								<div class="tutor-greetings-message tutor-fs-6 tutor-overflow-hidden tutor-text-justify">
									<?php esc_html_e( 'Wishing you a fantastic birthday! May your day be overflowing with happiness and laughter.Dive into this course and let it be a stepping stone for you to enhance your skills even more. Celebrate big today!', 'tutor' ); ?>
								</div>
								<div class="tutor-greetings-signature tutor-fs-6 tutor-text-right"><?php esc_html_e( '- Joe Nevarro', 'tutor' ); ?></div>
							</div>
						</div><!--/ tutor-greetings-card -->
					</div>
				</div> <!-- tutor-greetings-card-wrapper -->
				<div class="tutor-gifted-course-wrapper">
					<div class="tutor-gifted-course">
						<h3 class="tutor-gifted-course-title tutor-m-0 tutor-mb-12"><?php esc_html_e( 'Gifted Course', 'tutor' ); ?></h3>
						<div class="tutor-gifted-course-card">
							<div class="tutor-gifted-course-image tutor-flex-center tutor-w-s">
								<img src="https://picsum.photos/500/200" alt="Architectural Sketching" />
							</div>
							<div class="tutor-gifted-course-content">
								<h4 class="tutor-gifted-course-name tutor-fs-6 tutor-fw-bold"><?php esc_html_e( 'Architectural Sketching with Procreate', 'tutor' ); ?></h4>
								<p class="tutor-gifted-course-author tutor-fw-bold tutor-m-0"><?php esc_html_e( 'By', 'tutor' ); ?> <a href="#"><?php esc_html_e( 'Alice Grey', 'tutor' ); ?></a></p>
								<div class="tutor-gifted-course-rating tutor-d-flex tutor-align-center tutor-mt-16">
									<span class="tutor-gifted-rating-value"><?php esc_html_e( '5.0', 'tutor' ); ?></span>
									<div class="tutor-d-flex tutor-gifted-rating-stars-wrapper">
										<i class="tutor-icon-star-line tutor-gifted-rating-stars" data-rating-value="1"></i>
										<i class="tutor-icon-star-line tutor-gifted-rating-stars" data-rating-value="2"></i>
										<i class="tutor-icon-star-line tutor-gifted-rating-stars" data-rating-value="3"></i>
										<i class="tutor-icon-star-line tutor-gifted-rating-stars" data-rating-value="4"></i>
										<i class="tutor-icon-star-line tutor-gifted-rating-stars" data-rating-value="5"></i>
									</div>
								</div>
							</div> <!--/ tutor-gifted-course-content -->
						</div>
						<button class="tutor-btn tutor-btn-primary tutor-w-100 tutor-justify-center"><?php esc_html_e( 'Start Learning', 'tutor' ); ?></button>
					</div><!--/ tutor-gifted-course -->
				</div> <!--/ tutor-gifted-course-wrapper -->
			</div> <!--/ modal body -->

		</div><!--/ modal content -->
	</div>
</div>