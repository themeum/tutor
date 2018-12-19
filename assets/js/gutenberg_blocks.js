var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType;


/**
 * Block for student registration
 *
 */
registerBlockType( 'tutor-gutenberg/student-registration', {
    title: 'Tutor Student Registration',
    icon: 'welcome-learn-more',
    category: 'tutor',
    edit: function() {
        return el( 'div', {}, '[tutor_student_registration_form]' );
    },
    save: function() {
        return el( 'div', { }, '[tutor_student_registration_form]' );
    },
} );


registerBlockType( 'tutor-gutenberg/student-dashboard', {
    title: 'Tutor Student Dashboard',
    icon: 'welcome-learn-more',
    category: 'tutor',
    edit: function() {
        return el( 'div', {}, '[tutor_student_dashboard]' );
    },
    save: function() {
        return el( 'div', { }, '[tutor_student_dashboard]' );
    },
} );

registerBlockType( 'tutor-gutenberg/instructor-registration', {
    title: 'Instructor Registration Form',
    icon: 'welcome-learn-more',
    category: 'tutor',
    edit: function() {
        return el( 'div', {}, '[tutor_instructor_registration_form]' );
    },
    save: function() {
        return el( 'div', { }, '[tutor_instructor_registration_form]' );
    },
} );
