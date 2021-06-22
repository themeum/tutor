<?php
/**
 * Options generator
 */
?>

<!-- .tutor-backend-wrap -->
<section class="tutor-backend-wrap">
	<header class="tutor-option-header px-3 py-2">
		<div class="title">Settings</div>
		<div class="search-field">
			<div class="tutor-input-group tutor-form-control-has-icon">
				<span class="las la-search tutor-input-group-icon"></span>
				<input type="search" class="tutor-form-control" placeholder="Search" />
			</div>
		</div>
		<div class="save-button">
			<button class="tutor-btn">Save Changes</button>
		</div>
	</header>
	<!-- end /.tutor-option-header -->

	<!-- .tutor-option-body -->
	<div class="tutor-option-body">
		<form class="tutor-option-form py-4 px-3">
			<div class="tutor-option-tabs">
				<!-- .tutor-option-nav -->
				<ul class="tutor-option-nav">
					<li class="tutor-option-nav-item">
						<h4>Course</h4>
					</li>
					<li class="tutor-option-nav-item">
						<a href="#" data-tab="general">
							<img src="./assets/images/icons/general.svg" alt="general icon" />
							<span>General</span>
						</a>
					</li>
					<li class="tutor-option-nav-item">
						<a href="#" data-tab="course">
							<img src="./assets/images/icons/course.svg" alt="icon" />
							<span>Course</span>
						</a>
					</li>
					<li class="tutor-option-nav-item">
						<a href="#" data-tab="monitization">
							<img src="./assets/images/icons/monitization.svg" alt="monitization icon" />
							<span>Monitization</span>
						</a>
					</li>
					<li class="tutor-option-nav-item">
						<a href="#" data-tab="design" class="">
							<img src="./assets/images/icons/design.svg" alt="icon" />
							<span>Design</span>
						</a>
					</li>
					<li class="tutor-option-nav-item">
						<a href="#" data-tab="advance" class="">
							<img src="./assets/images/icons/advance.svg" alt="advance icon" />
							<span>Advance</span>
						</a>
					</li>
					<li class="tutor-option-nav-item">
						<a href="#" data-tab="email" class="">
							<img src="./assets/images/icons/email.svg" alt="email icon" />
							<span>Email</span>
						</a>
					</li>
					<li class="tutor-option-nav-item">
						<a href="#" data-tab="gradebook" class="">
							<img src="./assets/images/icons/gradebook.svg" alt="gradebook icon" />
							<span>Gradebook</span>
						</a>
					</li>
					<li class="tutor-option-nav-item">
						<a href="#" data-tab="certificate" class="active">
							<img src="./assets/images/icons/certificate.svg" alt="certificate icon" />
							<span>Certificate</span>
						</a>
					</li>
				</ul>
				<!-- end /.tutor-option-nav -->

				<!-- .tutor-option-nav -->
				<ul class="tutor-option-nav">
					<li class="tutor-option-nav-item">
						<h4>Tools</h4>
					</li>
					<li class="tutor-option-nav-item">
						<a href="#" data-tab="status">
							<img src="./assets/images/icons/status.svg" alt="icon" />
							<span>Status</span>
						</a>
					</li>
					<li class="tutor-option-nav-item">
						<a href="#" data-tab="import-export">
							<img src="./assets/images/icons/import-export.svg" alt="icon" />
							<span>Import/Export</span>
						</a>
					</li>
					<li class="tutor-option-nav-item">
						<a href="#" data-tab="setup-wizard">
							<img src="./assets/images/icons/setup-wizard.svg" alt="icon" />
							<span>Setup Wizard</span>
						</a>
					</li>
					<li class="tutor-option-nav-item">
						<a href="#" data-tab="tutor-pages">
							<img src="./assets/images/icons/tutor-pages.svg" alt="icon" />
							<span>Tutor Pages</span>
						</a>
					</li>
				</ul>
				<!-- end /.tutor-option-nav -->
			</div>
			<!-- end /.tutor-option-tabs -->

			<div class="tutor-option-tab-pages">
				<!-- #general .tutor-option-nav-page  -->
				<div id="general" class="tutor-option-nav-page">
					<!-- .tutor-option-main-title -->
					<div class="tutor-option-main-title">
						<h2>General</h2>
						<a href="#">
							<i class="las la-undo-alt"></i>
							Reset to Default
						</a>
					</div>
					<!-- end /.tutor-option-main-title -->

					<!-- .tutor-option-single-item  -->
					<div class="tutor-option-single-item">
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Dashboard Page</label>
									<p class="desc">
										This page will be used for student and instructor dashboard
									</p>
								</div>
								<div class="tutor-option-field-input">
									<select class="tutor-form-select">
										<option selected="">Dashboard Page</option>
										<option value="1">One</option>
										<option value="2">Two</option>
										<option value="3">Three</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  -->

					<!-- .tutor-option-single-item  Course -->
					<div class="tutor-option-single-item">
						<h4>Course</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Course Visibility</label>
									<p class="desc">Students must be logged in to view course</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<span class="label-before"> Logged Only </span>
										<input type="checkbox" class="tutor-form-toggle-input" />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Course Archive Page</label>
									<p class="desc">
										This page will be used to list all the published courses.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<select class="tutor-form-select">
										<option selected="">Archive Page</option>
										<option value="1">One</option>
										<option value="2">Two</option>
										<option value="3">Three</option>
									</select>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Course Content Access</label>
									<p class="desc">
										Allow instructors and admins to view the course content without
										enrolling
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>

							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Course Completion Process</label>
									<p class="desc">Content Needed Here.......</p>
								</div>
								<div class="tutor-option-field-input d-block">
									<div class="type-check d-block has-desc">
										<div class="tutor-form-check">
											<input
												type="radio"
												id="radio_x"
												class="tutor-form-check-input"
												name="radio_b"
												checked
											/>
											<label for="radio_x">
												Flexible
												<p class="desc">
													Allow instructors and admins to view the course content
													without enrolling
												</p>
											</label>
										</div>
										<div class="tutor-form-check">
											<input
												type="radio"
												id="radio_y"
												class="tutor-form-check-input"
												name="radio_b"
											/>
											<label for="radio_y">
												Strict Mode
												<p class="desc">
													Students have to complete, pass all the lessons and
													quizzes (if any) to mark a course as complete.
												</p>
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Course -->

					<!-- .tutor-option-single-item  Video -->
					<div class="tutor-option-single-item">
						<h4>Video</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Preferred Video Source</label>
									<p class="desc">
										Choose video sources you'd like to support. Unchecking all will not
										disable video feature.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<div class="type-check d-flex">
										<div class="tutor-form-check">
											<input
												type="checkbox"
												id="html5"
												class="tutor-form-check-input"
												name="html5"
											/>
											<label for="html5"> HTML 5 (mp4) </label>
										</div>
										<div class="tutor-form-check">
											<input
												type="checkbox"
												id="external-url"
												class="tutor-form-check-input"
												name="external-url"
											/>
											<label for="external-url"> External URL </label>
										</div>
										<div class="tutor-form-check">
											<input
												type="checkbox"
												id="youtube"
												class="tutor-form-check-input"
												name="youtube"
												checked
											/>
											<label for="youtube"> YouTube</label>
										</div>
										<div class="tutor-form-check">
											<input
												type="checkbox"
												id="vimeo"
												class="tutor-form-check-input"
												name="vimeo"
												checked
											/>
											<label for="vimeo"> Vimeo </label>
										</div>
									</div>
								</div>
							</div>
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Default Video Source</label>
									<p class="desc">
										This page will be used for student and instructor dashboard
									</p>
								</div>
								<div class="tutor-option-field-input">
									<select class="tutor-form-select">
										<option selected="">YouTube</option>
										<option value="1">One</option>
										<option value="2">Two</option>
										<option value="3">Three</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Video -->

					<!-- .tutor-option-single-item  Others-->
					<div class="tutor-option-single-item">
						<h4>Others</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Attachment Open Mode</label>
									<p class="desc">How you want users to view attached files.</p>
								</div>
								<div class="tutor-option-field-input">
									<div class="type-check d-flex">
										<div class="tutor-form-check">
											<input
												type="radio"
												id="attachment-open-mode-download"
												class="tutor-form-check-input"
												name="attachment-open-mode"
												checked
											/>
											<label for="attachment-open-mode-download"> Download </label>
										</div>
										<div class="tutor-form-check">
											<input
												type="radio"
												id="attachment-open-mode-new-tab"
												class="tutor-form-check-input"
												name="attachment-open-mode"
											/>
											<label for="attachment-open-mode-new-tab">
												View in new tab
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Enable Classic Editor Support</label>
									<p class="desc">
										Enable classic editor to get full support of any editor/page
										builder.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input
											type="checkbox"
											class="tutor-form-toggle-input"
											name="enable-classic-editor"
										/>
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
						</div>

						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Enable Marketplace</label>
									<p class="desc">Allow multiple instructors to upload their courses</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input
											type="checkbox"
											class="tutor-form-toggle-input"
											name="enable-marketplace"
										/>
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
						</div>

						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Lesson Permalink Base</label>
									<p class="desc">
										https://tut.sekander.pro/course/sample-course/<em>lessons</em>/sample-lesson/
									</p>
								</div>
								<div class="tutor-option-field-input">
									<input
										type="text"
										class="tutor-form-control"
										placeholder="lesson"
										value="lesson"
									/>
								</div>
							</div>
						</div>

						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Student registration page</label>
									<p class="desc">Choose the page for student registration page</p>
								</div>
								<div class="tutor-option-field-input">
									<select class="tutor-form-select">
										<option selected="">Student Login</option>
										<option value="1">One</option>
										<option value="2">Two</option>
										<option value="3">Three</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Others -->

					<!-- .tutor-option-single-item  Instructor-->
					<div class="tutor-option-single-item">
						<h4>Instructor</h4>

						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Instructor Registration Page</label>
									<p class="desc">This page will be used to sign up new instructors.</p>
								</div>
								<div class="tutor-option-field-input">
									<select class="tutor-form-select">
										<option selected="">Teacher Login</option>
										<option value="1">One</option>
										<option value="2">Two</option>
										<option value="3">Three</option>
									</select>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Allow Instructors Publishing Course</label>
									<p class="desc">
										Enable instructors to publish course directly. Do not select if
										admins want to review courses before publishing.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input
											type="checkbox"
											class="tutor-form-toggle-input"
											name="enable-marketplace"
											checked
										/>
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Become Instructor Button</label>
									<p class="desc">
										Uncheck this option to hide the button from student dashboard.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input
											type="checkbox"
											class="tutor-form-toggle-input"
											name="enable-marketplace"
											checked
										/>
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Instructor -->
				</div>
				<!-- end /#general .tutor-option-nav-page  -->

				<!-- #course .tutor-option-nav-page  -->
				<div id="course" class="tutor-option-nav-page">
					<!-- .tutor-option-main-title -->
					<div class="tutor-option-main-title">
						<h2>Course</h2>
						<a href="#">
							<i class="las la-undo-alt"></i>
							Reset to Default
						</a>
					</div>
					<!-- end /.tutor-option-main-title -->

					<!-- .tutor-option-single-item  Course -->
					<div class="tutor-option-single-item">
						<h4>Lesson</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Enable Video Player</label>
									<p class="desc">Content Needed Here.......</p>
								</div>
								<div class="tutor-option-field-input">
									<div class="type-check d-flex has-icon">
										<div class="tutor-form-check">
											<input
												type="checkbox"
												id="enable-youtube"
												class="tutor-form-check-input"
												name="enable-youtube"
											/>
											<label for="enable-youtube">
												<i class="lab la-youtube"></i>
												<span>Youtube</span>
											</label>
										</div>
										<div class="tutor-form-check">
											<input
												type="checkbox"
												id="enable-vimeo"
												class="tutor-form-check-input"
												name="enable-vimeo"
												checked
											/>
											<label for="enable-vimeo">
												<i class="lab la-vimeo"></i>
												<span>Vimeo</span>
											</label>
										</div>
									</div>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Course Visibility</label>
									<p class="desc">Students must be logged in to view course</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Course Content Access</label>
									<p class="desc">
										Allow instructors and admins to view the course content without
										enrolling
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Course -->

					<!-- .tutor-option-single-item  Course -->
					<div class="tutor-option-single-item">
						<h4>Quiz</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Time Limit</label>
									<p class="desc">Time <em>0</em> means unlimited time</p>
								</div>
								<div class="tutor-option-field-input d-flex justify-content-end">
									<input
										type="number"
										class="tutor-form-control"
										placeholder="0"
										value="0"
									/>
									<select class="tutor-form-select">
										<option selected="">Minutes</option>
										<option value="1">Hours</option>
										<option value="2">Months</option>
										<option value="3">Years</option>
									</select>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Time Limit</label>
									<p class="desc">Time <em>0</em> means unlimited time</p>
								</div>
								<div class="tutor-option-field-input">
									<div class="d-flex input-select">
										<input
											type="number"
											class="tutor-form-control"
											placeholder="0"
											value="0"
										/>
										<select class="tutor-form-select">
											<option selected="">Minutes</option>
											<option value="1">Hours</option>
											<option value="2">Months</option>
											<option value="3">Years</option>
										</select>
									</div>
								</div>
							</div>

							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>When time expires</label>
									<p class="desc">
										Choose which action to follow when the quiz time expires
									</p>
								</div>
								<div class="tutor-option-field-input">
									<div class="type-check d-block has-desc">
										<div class="tutor-form-check">
											<input
												type="radio"
												id="expire-time-1"
												class="tutor-form-check-input"
												name="expire-time"
												checked
											/>
											<label for="expire-time-1">
												Automatically
												<p class="desc">
													The current quiz answers are submitted automatically
												</p>
											</label>
										</div>
										<div class="tutor-form-check">
											<input
												type="radio"
												id="expire-time-2"
												class="tutor-form-check-input"
												name="expire-time"
											/>
											<label for="expire-time-2">
												Manual
												<p class="desc">
													The current quiz answers are submitted by students
												</p>
											</label>
										</div>
										<div class="tutor-form-check">
											<input
												type="radio"
												id="expire-time-3"
												class="tutor-form-check-input"
												name="expire-time"
											/>
											<label for="expire-time-3">
												Reject
												<p class="desc">
													Attempts must be submitted before time expires,
													otherwise they will not be counted
												</p>
											</label>
										</div>
									</div>
								</div>
							</div>

							<div class="tutor-option-field-row has-bg">
								<div class="tutor-option-field-label">
									<label>Attempts allowed</label>
									<p class="desc">
										The highest number of attempts students are allowed to take for a
										quiz. <em>0</em> means unlimited attempts
									</p>
								</div>
								<div class="tutor-option-field-input">
									<input
										type="number"
										class="tutor-form-control"
										placeholder="0"
										value="0"
									/>
								</div>
							</div>

							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Final grade calculation</label>
									<p class="desc">
										When multiple attempts are allowed, which method should be used to
										calculate a student's final grade for the quiz.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<div class="type-check d-flex">
										<div class="tutor-form-check">
											<input
												type="radio"
												id="grade-highest"
												class="tutor-form-check-input"
												name="grade"
												checked
											/>
											<label for="grade-highest"> Highest </label>
										</div>
										<div class="tutor-form-check">
											<input
												type="radio"
												id="grade-first"
												class="tutor-form-check-input"
												name="grade"
											/>
											<label for="grade-first"> First Attempt </label>
										</div>
										<div class="tutor-form-check">
											<input
												type="radio"
												id="grade-last"
												class="tutor-form-check-input"
												name="grade"
											/>
											<label for="grade-last"> Last Attempt </label>
										</div>
										<div class="tutor-form-check">
											<input
												type="radio"
												id="grade-average"
												class="tutor-form-check-input"
												name="grade"
											/>
											<label for="grade-average"> Average </label>
										</div>
									</div>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Course Content Access</label>
									<p class="desc">
										Allow instructors and admins to view the course content without
										enrolling
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Course -->
				</div>
				<!-- end /#course .tutor-option-nav-page  -->

				<!-- #monitization .tutor-option-nav-page  -->
				<div id="monitization" class="tutor-option-nav-page">
					<!-- .tutor-option-main-title -->
					<div class="tutor-option-main-title">
						<h2>Monetization</h2>
						<a href="#">
							<i class="las la-undo-alt"></i>
							Reset to Default
						</a>
					</div>
					<!-- end /.tutor-option-main-title -->

					<!-- .tutor-option-single-item  Monitization -->
					<div class="tutor-option-single-item">
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Disable Monetization</label>
									<p class="desc">Content Needed Here.......</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
						</div>
					</div>
					<!-- end .tutor-option-single-item  Monitization -->

					<!-- .tutor-option-single-item  Monitization -->
					<div class="tutor-option-single-item">
						<h4>Options</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Select eCommerce Engine</label>
									<p class="desc">Conent Needed Here.......</p>
								</div>
								<div class="tutor-option-field-input">
									<select class="tutor-form-select">
										<option selected="">WooCommerce Subscription</option>
										<option value="1">Option 1</option>
										<option value="2">Option 2</option>
									</select>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Enable Guest Mode</label>
									<p class="desc">Content Needed Here.......</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>

							<div class="tutor-option-field-row has-bg">
								<div class="tutor-option-field-label">
									<label>Enable Revenue Sharing</label>
									<p class="desc">Content Needed Here.......</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Sharing Percentage</label>
									<p class="desc">
										This page will be used for student and instructor dashboard
									</p>
								</div>
								<div class="tutor-option-field-input">
									<div class="d-flex flex-column double-input">
										<label for="revenue-instructor" class="revenue-percentage">
											<span>Instructor Takes</span>
											<input
												type="number"
												class="tutor-form-control"
												placeholder="0 %"
												value=""
												name="revenue-instructor"
												id="revenue-instructor"
											/>
										</label>
										<label for="revenue-admin" class="revenue-percentage">
											<span>Admin Takes</span>
											<input
												type="number"
												class="tutor-form-control"
												placeholder="0 %"
												value=""
												name="revenue-admin"
												id="revenue-admin"
											/>
										</label>
									</div>
								</div>
							</div>

							<div class="tutor-option-field-row has-bg">
								<div class="tutor-option-field-label">
									<label>Enable Revenue Sharing</label>
									<p class="desc">Content Needed Here.......</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Show statement per page</label>
									<p class="desc">Define the number of statements to show.</p>
								</div>
								<div class="tutor-option-field-input">
									<input
										type="number"
										class="tutor-form-control"
										placeholder="0"
										value="0"
									/>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Monitization -->

					<!-- .tutor-option-single-item  Monitization -->
					<div class="tutor-option-single-item">
						<h4>Fees</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Deduct Fees</label>
									<p class="desc">
										This page will be used for student and instructor dashboard
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>

							<div class="tutor-option-field-row col-1x1">
								<div class="tutor-option-field-label">
									<label>Fee Description</label>
									<p class="desc">This page will be used for student and instructor.</p>
								</div>
								<div class="tutor-option-field-input">
									<textarea
										name="fee-description"
										class="tutor-form-control"
										placeholder="Fee Description Here"
									></textarea>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Fee Amount & Type</label>
									<p class="desc">Conent Needed Here.......</p>
								</div>
								<div class="tutor-option-field-input d-flex justify-content-end">
									<select class="tutor-form-select">
										<option selected="">Select your Fee type</option>
										<option value="1">percent</option>
										<option value="2">fixed</option>
									</select>
									<input
										type="number"
										class="tutor-form-control"
										placeholder="0"
										value="0"
									/>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Monitization -->

					<!-- .tutor-option-single-item  Monitization -->
					<div class="tutor-option-single-item">
						<h4>Withdraw</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Minumum Withdrawal Amount</label>
									<p class="desc">
										Instructors should earn equal or above this amount to make a
										withdraw request.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<input type="number" class="tutor-form-control" placeholder="$0" />
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Minimum Days for Balance to be Available</label>
									<p class="desc">Conent Needed Here.......</p>
								</div>
								<div class="tutor-option-field-input">
									<input
										type="number"
										class="tutor-form-control"
										placeholder="0"
										value="0"
									/>
								</div>
							</div>

							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Enable withdraw method</label>
									<p class="desc">Conent Needed Here.......</p>
								</div>
								<div class="tutor-option-field-input">
									<div class="type-check d-flex">
										<div class="tutor-form-check">
											<input
												type="checkbox"
												id="withdraw-method-bank"
												class="tutor-form-check-input"
												name="withdraw-method-bank"
												checked
											/>
											<label for="withdraw-method-bank"> Bank Transfer </label>
										</div>
										<div class="tutor-form-check">
											<input
												type="checkbox"
												id="withdraw-method-paypal"
												class="tutor-form-check-input"
												name="withdraw-method-paypal"
											/>
											<label for="withdraw-method-paypal"> Paypal </label>
										</div>
										<div class="tutor-form-check">
											<input
												type="checkbox"
												id="withdraw-method-echeck"
												class="tutor-form-check-input"
												name="withdraw-method-echeck"
											/>
											<label for="withdraw-method-echeck"> eCheck </label>
										</div>
									</div>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Bank Instructions</label>
									<p class="desc">Conent Needed Here.......</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Monitization -->
				</div>
				<!-- end /#monitization .tutor-option-nav-page  -->

				<!-- #design .tutor-option-nav-page  -->
				<div id="design" class="tutor-option-nav-page active-">
					<!-- .tutor-option-main-title -->
					<div class="tutor-option-main-title">
						<h2>Design</h2>
						<a href="#">
							<i class="las la-undo-alt"></i>
							Reset to Default
						</a>
					</div>
					<!-- end /.tutor-option-main-title -->

					<!-- .tutor-option-single-item  Design (Course) -->
					<div class="tutor-option-single-item">
						<h4>Course</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Course Builder Page Logo</label>
								</div>
								<div class="tutor-option-field-input">
									<div class="d-flex logo-upload">
										<div class="logo-preview">
											<img
												src="./assets/images/icons/tutor-logo-course-builder.svg"
												alt="course builder logo"
											/>
											<span class="delete-btn"></span>
										</div>
										<div class="logo-upload-wrap">
											<p>
												Size: <strong>700x430 pixels;</strong> File Support:
												<strong>jpg, .jpeg or .png.</strong>
											</p>
											<label for="builder-logo-upload" class="tutor-btn">
												<input
													type="file"
													name="builder-logo-upload"
													id="builder-logo-upload"
												/>
												<span class="tutor-btn-icon las la-image"></span>
												<span>Upload Image</span>
											</label>
										</div>
									</div>
								</div>
							</div>

							<div class="tutor-option-field-row col-1x2">
								<div class="tutor-option-field-label">
									<label>Column Per Row</label>
									<p class="desc">
										Define how many column you want to use to display courses.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<div class="d-flex radio-thumbnail course-per-row">
										<label for="course-per-row-1" class="course-per-row-label">
											<input
												type="radio"
												name="course-per-row"
												id="course-per-row-1"
											/>
											<span class="icon-wrapper col-icon">
												<span>1</span>
											</span>
											<span class="title">One</span>
										</label>
										<label for="course-per-row-2" class="course-per-row-label">
											<input
												type="radio"
												name="course-per-row"
												id="course-per-row-2"
												checked
											/>
											<span class="icon-wrapper col-icon">
												<span>2</span>
												<span>2</span>
											</span>
											<span class="title">Two</span>
										</label>
										<label for="course-per-row-3" class="course-per-row-label">
											<input
												type="radio"
												name="course-per-row"
												id="course-per-row-3"
											/>
											<span class="icon-wrapper col-icon">
												<span>3</span>
												<span>3</span>
												<span>3</span>
											</span>
											<span class="title">Three</span>
										</label>
										<label for="course-per-row-4" class="course-per-row-label">
											<input
												type="radio"
												name="course-per-row"
												id="course-per-row-4"
											/>
											<span class="icon-wrapper col-icon">
												<span>4</span>
												<span>4</span>
												<span>4</span>
												<span>4</span>
											</span>
											<span class="title">Four</span>
										</label>
									</div>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Course Filter</label>
									<p class="desc">
										Show sorting and filtering options on course archive page
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>

							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Preferred Course Filters</label>
									<p class="desc">
										Choose preferred filter options you'd like to show in course archive
										page.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<div class="type-check d-flex">
										<div class="tutor-form-check">
											<input
												type="checkbox"
												class="tutor-form-check-input"
												id="course-filters-keyword"
												name="course-filters-keyword"
												checked
											/>
											<label for="course-filters-keyword"> Keyword Search </label>
										</div>
										<div class="tutor-form-check">
											<input
												type="checkbox"
												class="tutor-form-check-input"
												id="course-filters-category"
												name="course-filters-category"
											/>
											<label for="course-filters-category"> Category </label>
										</div>
										<div class="tutor-form-check">
											<input
												type="checkbox"
												class="tutor-form-check-input"
												id="course-filters-tag"
												name="course-filters-tag"
											/>
											<label for="course-filters-tag"> Tag </label>
										</div>
										<div class="tutor-form-check">
											<input
												type="checkbox"
												class="tutor-form-check-input"
												id="course-filters-difficulty-lavel"
												name="course-filters-difficulty-lavel"
											/>
											<label for="course-filters-difficulty-lavel">
												Difficulty Level
											</label>
										</div>
										<div class="tutor-form-check">
											<input
												type="checkbox"
												class="tutor-form-check-input"
												id="course-filters-price"
												name="course-filters-price"
											/>
											<label for="course-filters-price"> Price Type </label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Design (Course) -->

					<!-- .tutor-option-single-item  Design (Layout) -->
					<div class="tutor-option-single-item">
						<h4>Layout</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Instructor List Layout</label>
									<p class="desc">Content Needed Here............</p>
								</div>
								<div class="tutor-option-field-input">
									<div class="radio-thumbnail has-title instructor-list">
										<div class="vertical">
											<div class="layout-label">Vertical</div>
											<div class="d-flex- fields-wrapper">
												<label for="intructor-list-portrait">
													<input
														type="radio"
														name="instructor-list-layout"
														id="intructor-list-portrait"
														checked
													/>
													<span class="layout-icon icon-wrapper">
														<img
															src="./assets/images/instructor-layout/intructor-portrait.svg"
															alt=""
														/>
													</span>
													<span class="title">Portrait</span>
												</label>
												<label for="intructor-list-cover">
													<input
														type="radio"
														name="instructor-list-layout"
														id="intructor-list-cover"
													/>
													<span class="layout-icon icon-wrapper">
														<img
															src="./assets/images/instructor-layout/instructor-cover.svg"
															alt=""
														/>
													</span>
													<span class="title">Cover</span>
												</label>
												<label for="intructor-list-minimal">
													<input
														type="radio"
														name="instructor-list-layout"
														id="intructor-list-minimal"
													/>
													<span class="layout-icon icon-wrapper">
														<img
															src="./assets/images/instructor-layout/instructor-minimal.svg"
															alt=""
														/>
													</span>
													<span class="title">Minimal</span>
												</label>
											</div>
										</div>
										<div class="horizontal">
											<div class="layout-label">Horizontal</div>
											<div class="d-flex- fields-wrapper">
												<label for="intructor-list-horizontal-portrait">
													<input
														type="radio"
														name="instructor-list-layout"
														id="intructor-list-horizontal-portrait"
													/>
													<span class="icon-wrapper">
														<img
															src="./assets/images/instructor-layout/instructor-horizontal-portrait.svg"
															alt=""
														/>
													</span>
													<span class="title">Horizontal Portrait</span>
												</label>
												<label for="intructor-list-horizontal-minimal">
													<input
														type="radio"
														name="instructor-list-layout"
														id="intructor-list-horizontal-minimal"
													/>
													<span class="icon-wrapper">
														<img
															src="./assets/images/instructor-layout/instructor-horizontal-minimal.svg"
															alt=""
														/>
													</span>
													<span class="title">Horizontal Minimal</span>
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Public Profile Layout</label>
									<p class="desc">Content Needed Here............</p>
								</div>
								<div class="tutor-option-field-input">
									<div class="radio-thumbnail has-title public-profile fields-wrapper">
										<label for="profile-layout-modern">
											<input
												type="radio"
												name="profile-layout"
												id="profile-layout-modern"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/profile-layout/profile-modern.svg"
													alt=""
												/>
											</span>
											<span class="title">Modern</span>
										</label>
										<label for="profile-layout-minimal">
											<input
												type="radio"
												name="profile-layout"
												id="profile-layout-minimal"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/profile-layout/profile-minimal.svg"
													alt=""
												/>
											</span>
											<span class="title">Minimal</span>
										</label>
										<label for="profile-layout-classic">
											<input
												type="radio"
												name="profile-layout"
												id="profile-layout-classic"
												checked
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/profile-layout/profile-classic.svg"
													alt=""
												/>
											</span>
											<span class="title">Classic</span>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- /.tutor-option-single-item  Design (Layout) -->

					<!-- .tutor-option-single-item  Design (Course Details) -->
					<div class="tutor-option-single-item">
						<h4>Course Details</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-input">
								<div class="type-toggle-grid">
									<div class="toggle-item">
										<label class="tutor-form-toggle">
											<input type="checkbox" class="tutor-form-toggle-input" />
											<span class="tutor-form-toggle-control"></span>
											<span class="label-after"> Instructor Info </span>
										</label>
										<span
											class="tootip-wrapper"
											aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
										></span>
									</div>
									<div class="toggle-item">
										<label class="tutor-form-toggle">
											<input type="checkbox" class="tutor-form-toggle-input" />
											<span class="tutor-form-toggle-control"></span>
											<span class="label-after"> Question and Answer </span>
										</label>
										<span
											class="tootip-wrapper"
											aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit. Magni quod eligendi molestiae suscipit nostrum voluptatem, laborum corporis perferendis a. Illum."
										></span>
									</div>
									<div class="toggle-item">
										<label class="tutor-form-toggle">
											<input type="checkbox" class="tutor-form-toggle-input" />
											<span class="tutor-form-toggle-control"></span>
											<span class="label-after"> Author </span>
										</label>
										<span
											class="tootip-wrapper"
											aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
										></span>
									</div>
									<div class="toggle-item">
										<label class="tutor-form-toggle">
											<input type="checkbox" class="tutor-form-toggle-input" />
											<span class="tutor-form-toggle-control"></span>
											<span class="label-after"> Level </span>
										</label>
										<span
											class="tootip-wrapper"
											aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
										></span>
									</div>
									<div class="toggle-item">
										<label class="tutor-form-toggle">
											<input type="checkbox" class="tutor-form-toggle-input" />
											<span class="tutor-form-toggle-control"></span>
											<span class="label-after"> Social Share </span>
										</label>
										<span
											class="tootip-wrapper"
											aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
										></span>
									</div>
									<div class="toggle-item">
										<label class="tutor-form-toggle">
											<input type="checkbox" class="tutor-form-toggle-input" />
											<span class="tutor-form-toggle-control"></span>
											<span class="label-after"> Course Duration </span>
										</label>
										<span
											class="tootip-wrapper"
											aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
										></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Design (Course Details) -->
				</div>
				<!-- end /#design .tutor-option-nav-page  -->

				<!-- /#advance .tutor-option-nav-page  -->
				<div id="advance" class="tutor-option-nav-page active-">
					<!-- .tutor-option-main-title -->
					<div class="tutor-option-main-title">
						<h2>Advanced</h2>
						<a href="#">
							<i class="las la-undo-alt"></i>
							Reset to Default
						</a>
					</div>
					<!-- end /.tutor-option-main-title -->

					<!-- .tutor-option-single-item  Advance (Options) -->
					<div class="tutor-option-single-item">
						<h4>Options</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Error Message for Wrong Login Credentials</label>
									<p class="desc">
										Login error message displayed when the user puts wrong login
										credentials.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Hide Frontend Admin Bar</label>
									<p class="desc">
										Enable this to remove the frontend admin bar at the top of your
										website
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Maintenance Mode</label>
									<p class="desc">
										Enabling the maintenance mode allows you to display a custom message
										on the frontend. During this time, visitors can not access the site
										content. But the wp-admin dashboard will remain accessible.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Advance (Options) -->
				</div>
				<!-- end /#advance .tutor-option-nav-page  -->

				<!-- /#email .tutor-option-nav-page  -->
				<div id="email" class="tutor-option-nav-page">
					<!-- .tutor-option-main-title -->
					<div class="tutor-option-main-title">
						<h2>Email</h2>
						<a href="#">
							<i class="las la-undo-alt"></i>
							Reset to Default
						</a>
					</div>
					<!-- end /.tutor-option-main-title -->

					<!-- .tutor-option-single-item  Email (Email Settings) -->
					<div class="tutor-option-single-item">
						<h4>Email Settings</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row col-1x145">
								<div class="tutor-option-field-label">
									<label>Name</label>
									<p class="desc">The name under which all the emails will be sent</p>
								</div>
								<div class="tutor-option-field-input">
									<input
										type="text"
										name="email-settings-name"
										class="tutor-form-control"
										placeholder="Jhon Doe"
										value="Tutor LMS"
									/>
								</div>
							</div>
							<div class="tutor-option-field-row col-1x145">
								<div class="tutor-option-field-label">
									<label>E-Mail Address</label>
									<p class="desc">
										The E-Mail address from which all emails will be sent
									</p>
								</div>
								<div class="tutor-option-field-input">
									<input
										type="email"
										name="email-settings-email"
										class="tutor-form-control"
										placeholder="jhondoe@example.com"
										value="demo@test.com"
									/>
								</div>
							</div>
							<div class="tutor-option-field-row col-1x145">
								<div class="tutor-option-field-label">
									<label>Footer Text</label>
									<p class="desc">The text to appear in E-Mail template footer</p>
								</div>
								<div class="tutor-option-field-input">
									<textarea
										name="email-settings-textarea"
										class="tutor-form-control"
										placeholder="This text will show in the footer"
									>
demo@test.com</textarea
									>
								</div>
							</div>

							<div class="tutor-option-field-row input-field-code">
								<div class="tutor-option-field-label">
									<label>Mailer Native Server Cron</label>
									<p class="desc">If you use OS native cron, then disable it.</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked="" />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
								<div class="tutor-option-field-input code">
									<textarea
										name="email-settings-textarea-code"
										class="tutor-form-control"
										placeholder="Mailer Native Server Cron"
										readonly
									>
										yes="{\"call_again\":\"yes\"}"
										call_again="$yes"
										while [[ "$call_again" == "$yes" ]]; do
										call_again=$(curl -L "site_url_base/?tutor_cd_cron_type=os_native")
										done
									</textarea>
									<!-- <span class="code-copy-btn"><i class="las la-clipboard-list"></i>Copy</span> -->
									<button class="tutor-btn tutor-is-outline tutor-is-xs code-copy-btn">
										<span class="tutor-btn-icon las la-clipboard-list"></span>
										<span>Copy</span>
									</button>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Email (Email Settings) -->

					<!-- .tutor-option-single-item  Email (E-Mail to Students) -->
					<div class="tutor-option-single-item">
						<h4>E-Mail to Students</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label has-tooltip">
									<label>Course Enrolled</label>
									<span
										class="tootip-wrapper"
										aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
									></span>
								</div>
								<div class="tutor-option-field-input d-flex has-btn-after">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
									<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">
										Edit
									</button>
								</div>
							</div>
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label has-tooltip">
									<label>Quiz Completed</label>
									<span
										class="tootip-wrapper"
										aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
									></span>
								</div>
								<div class="tutor-option-field-input d-flex has-btn-after">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
									<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">
										Edit
									</button>
								</div>
							</div>
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label has-tooltip">
									<label>Completed a Course</label>
									<span
										class="tootip-wrapper"
										aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
									></span>
								</div>
								<div class="tutor-option-field-input d-flex has-btn-after">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
									<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">
										Edit
									</button>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Email (E-Mail to Students) -->

					<!-- .tutor-option-single-item  Email (E-Mail to Teachers) -->
					<div class="tutor-option-single-item">
						<h4>E-Mail to Teachers</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label has-tooltip">
									<label>A Student Enrolled in Course</label>
									<span
										class="tootip-wrapper"
										aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
									></span>
								</div>
								<div class="tutor-option-field-input d-flex has-btn-after">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" />
										<span class="tutor-form-toggle-control"></span>
									</label>
									<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">
										Edit
									</button>
								</div>
							</div>
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label has-tooltip">
									<label>A Student Completed Course</label>
									<span
										class="tootip-wrapper"
										aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
									></span>
								</div>
								<div class="tutor-option-field-input d-flex has-btn-after">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" />
										<span class="tutor-form-toggle-control"></span>
									</label>
									<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">
										Edit
									</button>
								</div>
							</div>
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label has-tooltip">
									<label>A Student Completed Lesson</label>
									<span
										class="tootip-wrapper"
										aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
									></span>
								</div>
								<div class="tutor-option-field-input d-flex has-btn-after">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" />
										<span class="tutor-form-toggle-control"></span>
									</label>
									<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">
										Edit
									</button>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Email (E-Mail to Teachers) -->

					<!-- .tutor-option-single-item  Email (E-Mail to Admin) -->
					<div class="tutor-option-single-item">
						<h4>E-Mail to Admin</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label has-tooltip">
									<label>New Instructor Signup</label>
									<span
										class="tootip-wrapper"
										aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
									></span>
								</div>
								<div class="tutor-option-field-input d-flex has-btn-after">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
									<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">
										Edit
									</button>
								</div>
							</div>
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label has-tooltip">
									<label>New Student Signup</label>
									<span
										class="tootip-wrapper"
										aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
									></span>
								</div>
								<div class="tutor-option-field-input d-flex has-btn-after">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
									<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">
										Edit
									</button>
								</div>
							</div>
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label has-tooltip">
									<label>New Course Submitted for Review</label>
									<span
										class="tootip-wrapper"
										aria-label="Lorem ipsum dolor sit amet consectetur adipisicing elit."
									></span>
								</div>
								<div class="tutor-option-field-input d-flex has-btn-after">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
									<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">
										Edit
									</button>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  Email (E-Mail to Admin) -->
				</div>
				<!-- end /#email .tutor-option-nav-page  -->

				<!-- /#gradebook .tutor-option-nav-page  -->
				<div id="gradebook" class="tutor-option-nav-page">
					<!-- .tutor-option-main-title -->
					<div class="tutor-option-main-title">
						<h2>Gradebook</h2>
						<a href="#">
							<i class="las la-undo-alt"></i>
							Reset to Default
						</a>
					</div>
					<!-- end /.tutor-option-main-title -->

					<!-- .tutor-option-single-item  (Gradebook) -->
					<div class="tutor-option-single-item">
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Use Points Instead of Grades</label>
									<p class="desc">
										Enable this option to use numerical points instead of letter grades.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  (Gradebook) -->

					<!-- .tutor-option-single-item  (Gradebook) -->
					<div class="tutor-option-single-item">
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Show Highest Possible Points</label>
									<p class="desc">
										Display the highest possible points next to a student’s score such
										as 3.8/4.0
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  (Gradebook) -->

					<!-- .tutor-option-single-item  (Gradebook) -->
					<div class="tutor-option-single-item">
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Separator Between Scores</label>
									<p class="desc">
										Input the separator text or symbol to display. Example: Insert / to
										display 3.8/4.0 or “out of” 3.8 out of 4.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<input
										type="text"
										class="tutor-form-control"
										placeholder="e.g: /, ., -, :"
										value="/"
										style="max-width: 90px; text-align: center"
									/>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  (Gradebook) -->

					<!-- .tutor-option-single-item  (Gradebook) -->
					<div class="tutor-option-single-item">
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Grade Scale</label>
									<p class="desc">
										Insert the grade point out of which the final results will be
										calculated.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<input
										type="text"
										class="tutor-form-control"
										placeholder="5.00"
										value="4.00"
										style="max-width: 90px; text-align: center"
									/>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  (Gradebook) -->
				</div>
				<!-- end /#gradebook .tutor-option-nav-page  -->

				<!-- /#certificate  .tutor-option-nav-page -->
				<div id="certificate" class="tutor-option-nav-page active">
					<!-- .tutor-option-main-title -->
					<div class="tutor-option-main-title">
						<h2>Certificate</h2>
						<a href="#">
							<i class="las la-undo-alt"></i>
							Reset to Default
						</a>
					</div>
					<!-- end /.tutor-option-main-title -->

					<!-- .tutor-option-single-item  (Certificate) -->
					<div class="tutor-option-single-item create-certificate-steps">
						<div class="item-wrapper">
							<h4>
								Create Your Certificate <br />
								In 3 Steps
							</h4>
							<ul>
								<li>Select your favorite design</li>
								<li>Type in your text & upload your signature</li>
								<li><strong>Press Save,</strong>Your certificate Ready</li>
							</ul>
							<div class="create-certificate-btn">
								<button class="tutor-btn">Create Certificate</button>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  (Certificate) -->

					<!-- .tutor-option-single-item  (Certificate) -->
					<div class="tutor-option-single-item all-certificate">
						<h4>All Certificate</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="certificate-thumb">
									<img
										src="./assets/images/certificate-thumb/cetificate-thumb-1.jpg"
										alt=""
									/>
								</div>
								<div class="tutor-option-field-label">
									<label
										>Write PHP Like a Pro: Build a PHP MVC Framework From Scratch
									</label>
									<p class="desc">Categorey: Design, Illustration</p>
								</div>
								<div class="tutor-option-field-input d-flex has-btn-after">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
									<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">
										Edit
									</button>
								</div>
							</div>
							<div class="tutor-option-field-row">
								<div class="certificate-thumb">
									<img
										src="./assets/images/certificate-thumb/cetificate-thumb-2.jpg"
										alt=""
									/>
								</div>
								<div class="tutor-option-field-label">
									<label
										>Machine Learning A-Z: Hands-On Python & R In Data Science
									</label>
									<p class="desc">Categorey: Design, Illustration</p>
								</div>
								<div class="tutor-option-field-input d-flex has-btn-after">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
									<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">
										Edit
									</button>
								</div>
							</div>
							<div class="tutor-option-field-row">
								<div class="certificate-thumb">
									<img
										src="./assets/images/certificate-thumb/cetificate-thumb-3.jpg"
										alt=""
									/>
								</div>
								<div class="tutor-option-field-label">
									<label>The Ultimate Drawing Course - Beginner to Advanced </label>
									<p class="desc">Categorey: Design, Illustration</p>
								</div>
								<div class="tutor-option-field-input d-flex has-btn-after">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
									<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">
										Edit
									</button>
								</div>
							</div>
							<div class="tutor-option-field-row">
								<div class="certificate-thumb">
									<img
										src="./assets/images/certificate-thumb/cetificate-thumb-4.jpg"
										alt=""
									/>
								</div>
								<div class="tutor-option-field-label">
									<label>Master Procedural Maze & Dungeon Generation </label>
									<p class="desc">Categorey: Design, Illustration</p>
								</div>
								<div class="tutor-option-field-input d-flex has-btn-after">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
									<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">
										Edit
									</button>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  (Certificate) -->

					<!-- .tutor-option-single-item  (Certificate) -->
					<div class="tutor-option-single-item no-certificate">
						<h4>No Certificate</h4>
						<div class="item-wrapper">
							<div class="certificate-thumb">
								<img
									src="./assets/images/certificate-thumb/no-certificate-thumb.svg"
									alt=""
								/>
								<p>No certificate generate yet</p>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  (Certificate) -->

					<!-- .tutor-option-single-item  (Certificate) -->
					<div class="tutor-option-single-item certificate-template">
						<h4>Select Certificate Template</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Horizontal Template</label>
								</div>
								<div class="tutor-option-field-input">
									<div class="radio-thumbnail horizontal">
										<label for="certificate-template-horizontal-1">
											<input
												type="radio"
												name="certificate-template"
												id="certificate-template-horizontal-1"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/certificate-template/certificate-horizontal-1.jpg"
													alt=""
												/>
											</span>
										</label>
										<label for="certificate-template-horizontal-2">
											<input
												type="radio"
												name="certificate-template"
												id="certificate-template-horizontal-2"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/certificate-template/certificate-horizontal-2.jpg"
													alt=""
												/>
											</span>
										</label>
										<label for="certificate-template-horizontal-3">
											<input
												type="radio"
												name="certificate-template"
												id="certificate-template-horizontal-3"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/certificate-template/certificate-horizontal-3.jpg"
													alt=""
												/>
											</span>
										</label>
										<label for="certificate-template-horizontal-4">
											<input
												type="radio"
												name="certificate-template"
												id="certificate-template-horizontal-4"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/certificate-template/certificate-horizontal-4.jpg"
													alt=""
												/>
											</span>
										</label>
										<label for="certificate-template-horizontal-5">
											<input
												type="radio"
												name="certificate-template"
												id="certificate-template-horizontal-5"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/certificate-template/certificate-horizontal-5.jpg"
													alt=""
												/>
											</span>
										</label>
										<label for="certificate-template-horizontal-6">
											<input
												type="radio"
												name="certificate-template"
												id="certificate-template-horizontal-6"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/certificate-template/certificate-horizontal-6.jpg"
													alt=""
												/>
											</span>
										</label>
										<label for="certificate-template-horizontal-7">
											<input
												type="radio"
												name="certificate-template"
												id="certificate-template-horizontal-7"
												checked
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/certificate-template/certificate-horizontal-7.jpg"
													alt=""
												/>
											</span>
										</label>
									</div>
								</div>
							</div>
							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Vertical Template</label>
								</div>
								<div class="tutor-option-field-input">
									<div class="radio-thumbnail vertical">
										<label for="certificate-template-vertical-1">
											<input
												type="radio"
												name="certificate-template"
												id="certificate-template-vertical-1"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/certificate-template/certificate-vertical-1.jpg"
													alt=""
												/>
											</span>
										</label>
										<label for="certificate-template-vertical-2">
											<input
												type="radio"
												name="certificate-template"
												id="certificate-template-vertical-2"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/certificate-template/certificate-vertical-2.jpg"
													alt=""
												/>
											</span>
										</label>
										<label for="certificate-template-vertical-3">
											<input
												type="radio"
												name="certificate-template"
												id="certificate-template-vertical-3"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/certificate-template/certificate-vertical-3.jpg"
													alt=""
												/>
											</span>
										</label>
										<label for="certificate-template-vertical-4">
											<input
												type="radio"
												name="certificate-template"
												id="certificate-template-vertical-4"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/certificate-template/certificate-vertical-4.jpg"
													alt=""
												/>
											</span>
										</label>
										<label for="certificate-template-vertical-5">
											<input
												type="radio"
												name="certificate-template"
												id="certificate-template-vertical-5"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/certificate-template/certificate-vertical-5.jpg"
													alt=""
												/>
											</span>
										</label>
										<label for="certificate-template-vertical-6">
											<input
												type="radio"
												name="certificate-template"
												id="certificate-template-vertical-6"
											/>
											<span class="icon-wrapper">
												<img
													src="./assets/images/certificate-template/certificate-vertical-6.jpg"
													alt=""
												/>
											</span>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  (Certificate) -->

					<!-- .tutor-option-single-item  (Certificate) -->
					<div class="tutor-option-single-item certificate-settings">
						<h4>Certifiate Settings</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>Add Instructor Info</label>
									<p class="desc">
										Enable to add course instructor’s information on all generated
										certificates.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>

							<div class="tutor-option-field-row col-1x145">
								<div class="tutor-option-field-label">
									<label>Authorised Name</label>
									<p class="desc">
										This name represents that you (the admin) have authorized this
										certificate to the student.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<input
										type="text"
										name="email-settings-name"
										class="tutor-form-control"
										placeholder="Jhon Doe"
										value="Tutor LMS"
									/>
								</div>
							</div>

							<div class="tutor-option-field-row col-1x145">
								<div class="tutor-option-field-label">
									<label>Authorised Company Name</label>
									<p class="desc">
										Add your eLearning company name below your authorized name to add
										credibility to the certificates.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<input
										type="text"
										name="email-settings-name"
										class="tutor-form-control"
										placeholder="Company Name"
										value="demo@test.com"
									/>
								</div>
							</div>

							<div class="tutor-option-field-row col-1x145">
								<div class="tutor-option-field-label">
									<label>Signature</label>
									<p class="desc">
										Upload a signature that will be printed on the certificate.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<div class="signature-upload-wrap">
										<div class="signature-upload">
											<div class="signature-preview">
												<img
													src="./assets/images/signature-demo.svg"
													alt="signature preview"
												/>
												<span class="delete-btn"></span>
											</div>
											<div class="signature-info">
												<p style="font-size: 16px">
													File Support:
													<strong style="font-weight: 500"
														>jpg, .jpeg or .png.</strong
													>
												</p>
												<p style="font-size: 13px">Size: 700x430 pixels;</p>
											</div>
										</div>
										<label for="signature-uploader" class="tutor-btn">
											<input
												type="file"
												name="signature-uploader"
												id="signature-uploader"
											/>
											<span class="tutor-btn-icon las la-image"></span>
											<span>Upload Image</span>
										</label>
									</div>
								</div>
							</div>

							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<label>View Certificate</label>
									<p class="desc">
										Enable to generate a publicly accessible URL that students can use
										to verify their certificates.
									</p>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input type="checkbox" class="tutor-form-toggle-input" checked />
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
						</div>
					</div>
					<!-- end /.tutor-option-single-item  (Certificate) -->
				</div>
				<!-- end /#certificate  .tutor-option-nav-page -->

				<!-- Custom markup variations -->
				<div id="status" class="tutor-option-nav-page active-">
					<div class="tutor-option-main-title">
						<h2>Custom markup</h2>
						<a href="#">
							<i class="las la-undo-alt"></i>
							Reset to Default
						</a>
					</div>
					<!-- end /.tutor-option-main-title -->

					<div class="tutor-option-single-item">
						<h4>Input field variations</h4>
						<div class="item-wrapper">
							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Radio type with <em>block row, flex label</em></label>
									<p class="desc">
										Lorem ipsum dolor sit amet consectetur adipisicing elit.
									</p>
								</div>
								<!-- end /.tutor-option-field-label -->
								<div class="tutor-option-field-input">
									<div class="type-check d-flex">
										<div class="tutor-form-check">
											<input
												type="radio"
												class="tutor-form-check-input"
												name="ooooo"
												id="aaaaa"
												checked
											/>
											<label for="aaaaa"> Lorem </label>
										</div>
										<div class="tutor-form-check">
											<input
												type="radio"
												class="tutor-form-check-input"
												name="ooooo"
												id="bbbbb"
												checked
											/>
											<label for="bbbbb"> ipsum </label>
										</div>
										<div class="tutor-form-check">
											<input
												type="radio"
												class="tutor-form-check-input"
												name="ooooo"
												id="ccccc"
												checked
											/>
											<label for="ccccc"> dolor </label>
										</div>
									</div>
								</div>
								<!-- end /.tutor-option-field-input -->
							</div>
							<!-- end /.tutor-option-field-row -->

							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Radio type with <em>block row, block description</em></label>
									<p class="desc">
										Lorem ipsum dolor sit amet consectetur adipisicing elit.
									</p>
								</div>
								<!-- end /.tutor-option-field-label -->
								<div class="tutor-option-field-input">
									<div class="type-check d-block has-desc">
										<div class="tutor-form-check">
											<input
												type="radio"
												class="tutor-form-check-input"
												name="ooooo2"
												id="aaaaa2"
												checked
											/>
											<label for="aaaaa2" class="d-block">
												Automatically
												<p class="desc">
													The current quiz answers are submitted automatically
												</p>
											</label>
										</div>
										<div class="tutor-form-check">
											<input
												type="radio"
												class="tutor-form-check-input"
												name="ooooo2"
												id="bbbbb2"
												checked
											/>
											<label for="bbbbb2" class="d-block">
												aasdkjfasldkjfl
												<p class="desc">
													The current quiz answers are submitted automatically
												</p>
											</label>
										</div>
										<div class="tutor-form-check">
											<input
												type="radio"
												class="tutor-form-check-input"
												name="ooooo2"
												id="ccccc2"
												checked
											/>
											<label for="ccccc2" class="d-block">
												llkjieiufiaafei
												<p class="desc">
													The current quiz answers are submitted automatically
												</p>
											</label>
										</div>
									</div>
								</div>
								<!-- end /.tutor-option-field-input -->
							</div>
							<!-- end /.tutor-option-field-row -->
							<div class="tutor-option-field-row d-block">
								<div class="tutor-option-field-label">
									<label>Checkbox with <em>block row, flex label icon</em></label>
									<p class="desc">
										Lorem ipsum dolor sit amet consectetur adipisicing elit.
									</p>
								</div>
								<!-- end /.tutor-option-field-label -->
								<div class="tutor-option-field-input">
									<div class="type-check d-flex has-icon">
										<div class="tutor-form-check">
											<input
												type="checkbox"
												class="tutor-form-check-input"
												id="checkbox1"
												name="checkbox1"
											/>
											<label for="checkbox1">
												<i class="lab la-youtube"></i>
												<span>checkbox 1</span>
											</label>
										</div>

										<div class="tutor-form-check">
											<input
												type="checkbox"
												class="tutor-form-check-input"
												id="checkbox2"
												name="checkbox2"
											/>
											<label for="checkbox2">
												<i class="lab la-vimeo"></i>
												<span>checkbox 2</span>
											</label>
										</div>
									</div>
								</div>
								<!-- end /.tutor-option-field-input -->
							</div>
							<!-- end /.tutor-option-field-row -->
						</div>
						<!-- end /.item-wrapper -->
					</div>
					<!-- end /.tutor-option-single-item -->
				</div>
				<!-- end /.tutor-option-nav-page -->

				<div id="import-export" class="tutor-option-nav-page">
					#import-export <br />
					repellendus, accusamus nam omnis fugit nostrum odio laudantium nisi autem iusto?
					Aspernatur, sint architecto! Nobis, ex fugit veritatis voluptate distinctio natus quod,
					similique saepe illum beatae quo nesciunt. Aliquam nam, autem, nulla expedita quas
					reiciendis ipsa dolores voluptas, harum nesciunt aspernatur dignissimos quam enim
					suscipit. Quis reprehenderit nisi reiciendis explicabo velit, aspernatur numquam dolore
					voluptatem deserunt amet commodi ea aut a saepe tenetur cum nihil, voluptas sapiente
					temporibus praesentium sunt obcaecati.
				</div>
				<div id="setup-wizard" class="tutor-option-nav-page">
					#setup-wizard <br />
					inventore molestiae, veritatis molestias aperiam vel ipsa ullam sapiente eum facere
					illo, soluta blanditiis nemo consequuntur omnis aliquid ex? Consequuntur et deleniti,
					doloribus quisquam autem quia a id suscipit repudiandae quae voluptatem unde illum
					labore officiis cumque facere illo incidunt ea quod? Expedita libero rerum consequuntur
					magni perspiciatis quisquam nam eveniet excepturi accusamus. Explicabo, magnam. Aliquid
					est perferendis assumenda labore itaque nostrum reprehenderit dolorum quos debitis, non
					omnis ducimus ad id cupiditate. Error?
				</div>

				<div id="tutor-pages" class="tutor-option-nav-page">
					#tutor-pages <br />
					ea quod? Expedita libero rerum consequuntur magni perspiciatis quisquam nam eveniet
					excepturi accusamus. Explicabo, magnam. Aliquid est perferendis assumenda labore itaque
					nostrum reprehenderit dolorum quos debitis, non omnis ducimus ad id cupiditate.
					Error?inventore molestiae, veritatis molestias aperiam vel ipsa ullam sapiente eum
					facere illo, soluta blanditiis nemo consequuntur omnis aliquid ex? Consequuntur et
					deleniti, doloribus quisquam autem quia a id suscipit repudiandae quae voluptatem unde
					illum labore officiis cumque facere illo incidunt
				</div>
			</div>
			<!-- end /.tutor-option-tab-pages -->
		</form>
	</div>
	<!-- end /.tutor-option-body -->
</section>
<!-- end /.tutor-backend-wrap -->


