<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Course_Filter{
    private $category = 'course-category';
    private $tag = 'course-tag';
    private $current_term_id = null;

    function __construct(){
        add_action('wp_ajax_tutor_course_filter_ajax', array($this, 'load_listing'));
        add_action('wp_ajax_nopriv_tutor_course_filter_ajax', array($this, 'load_listing'));
    }

    public function load_listing(){
		tutils()->checking_nonce();

		$courses_per_page = isset($_POST['course_per_page']) ? $_POST['course_per_page'] : tutils()->get_option('courses_per_page', 6);
        $page = (isset($_POST['page']) && is_numeric($_POST['page']) && $_POST['page']>0) ? $_POST['page'] : 1;

        $args = array(
            'post_status' => 'publish',
            'post_type' => 'courses',
            'posts_per_page' => $courses_per_page,
            'paged' => $page,
            'tax_query'=> array(
                'relation' => 'OR',
            )
        );
        // Prepare taxonomy
        $tax_query = array();
        foreach(['category', 'tag'] as $taxonomy){
            if(isset($_POST['tutor-course-filter-'.$taxonomy]) && count($_POST['tutor-course-filter-'.$taxonomy])>0){
                $tax_query =array(
                    'taxonomy' => $this->$taxonomy,
                    'field' => 'term_id',
                    'terms' => $_POST['tutor-course-filter-'.$taxonomy],
                    'operator' => 'IN'
                );
                array_push($args['tax_query'],$tax_query);
            }
        }

        // Prepare level and price type
        $is_membership = get_tutor_option('monetize_by')=='pmpro' && tutils()->has_pmpro();
        $level_price=array();
        foreach(['level', 'price'] as $type){
            
            if($is_membership && $type=='price'){
                continue;
            }

            if(isset($_POST['tutor-course-filter-'.$type]) && count($_POST['tutor-course-filter-'.$type])>0){
                $level_price[]=array(
                    'key' => $type=='level' ? '_tutor_course_level' : '_tutor_course_price_type',
                    'value' => $_POST['tutor-course-filter-'.$type],
                    'compare' => 'IN'
                );
            }
        }
        count($level_price) ? $args['meta_query']=$level_price : 0;
        isset($_POST['keyword']) ? $args['s']=$_POST['keyword'] : 0;

        if(isset($_POST['tutor_course_filter'])){
            switch ($_POST['tutor_course_filter']){
                case 'newest_first':
                    $args['orderby']='ID';
                    $args['order']='desc';
                    break;
                case 'oldest_first':
                    $args['orderby']='ID';
                    $args['order']='asc';
                    break;
                case 'course_title_az':
                    $args['orderby']='post_title';
                    $args['order']='asc';
                    break;
                case 'course_title_za':
                    $args['orderby']='post_title';
                    $args['order']='desc';
                    break;
            }
        }

        query_posts($args);
		$GLOBALS['tutor_shortcode_arg']=array(
			'column_per_row' => $_POST['column_per_row'],
			'course_per_page' => $courses_per_page
		);
		
        tutor_load_template('archive-course-init');
        exit;
    }

    private function get_current_term_id(){
        
        if($this->current_term_id===null){
            $queried = get_queried_object();
            $this->current_term_id = (is_object($queried) && property_exists($queried, 'term_id')) ? $queried->term_id : false;
        }
        
        return $this->current_term_id;
    }

    public function render_terms($taxonomy){

        $term_id = $this->get_current_term_id();
        $terms = get_terms( array('taxonomy' => $this->$taxonomy, 'hide_empty' => true));

        foreach($terms as $term){
            ?>
                <label>
                    <input type="checkbox" name="tutor-course-filter-<?php echo $taxonomy; ?>" value="<?php echo $term->term_id; ?>" <?php echo $term->term_id==$term_id ? 'checked="checked"' : ''; ?>/>&nbsp;
                    <?php echo $term->name; ?>
                </label>
            <?php
        }
    }
}