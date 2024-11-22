<?php
/*
Plugin Name: ACF Course Calculator
Description: Individuelles Plugin zur Berechnung der Kurskosten und Rabatte, einzubinden auf Kurseiten
Version: 1.0
*/

function acf_course_calculator() {
    if (!function_exists('get_field')) {
        return '<p>Bitte installiere und aktiviere das ACF-Plugin.</p>';
    }

    wp_enqueue_style('calculator-styles', plugin_dir_url(__FILE__) . 'assets/calculator-styles.css');
    wp_enqueue_script('calculator-script', plugin_dir_url(__FILE__) . 'assets/calculator-script.js', array(), null, true);

    $modulesStart = array();
    $modulesGoal = array();
    $listDiscount = array();

    if (have_rows('course-niveau-start', 'option')) {
        while (have_rows('course-niveau-start', 'option')) : the_row();
            $modulesStart[] = array(
                "name"  => get_sub_field('name', 'option'),
                "value" => get_sub_field('preis', 'option'),
            );
        endwhile;
    }

    if (have_rows('course-niveau-goal', 'option')) {
        while (have_rows('course-niveau-goal', 'option')) : the_row();
            $modulesGoal[] = array(
                "name"  => get_sub_field('name', 'option'),
                "value" => get_sub_field('duration', 'option'),
            );
        endwhile;
    }

    if (have_rows('rabatte', 'option')) {
        while (have_rows('rabatte', 'option')) : the_row();
            $listDiscount[] = array(
                "nr"       => get_sub_field('monat', 'option'),
                "discount" => get_sub_field('rabatt-einzeln', 'option'),
            );
        endwhile;
    }

    wp_localize_script('calculator-script', 'acfCourseData', array(
        'moduleDataStart' => $modulesStart,
        'moduleDataGoal'  => $modulesGoal,
        'discountData'    => $listDiscount,
    ));

    // labels
    // Headline
    $headline = get_field('calculator_headline','option');
    $labelModuleStart = get_field('label-module-start','option');
    $labelModuleGoal = get_field('label-module-goal','option');
    $labelRegPrice = esc_html(get_field('label-reg-price','option'));
    $labelDiscount = esc_html(get_field('label-dicount','option'));
    $labelResult = esc_html(get_field('label-result','option'));
   

    ob_start(); ?>
    <form id="courseCalculator">
        <?php if($headline) : ?>
            <legend><?php echo esc_html($headline);?></legend>
        <?php endif; ?>
        <div class="form-group my-3">
            <label for="moduleStart"><strong><?php echo (esc_html($labelModuleStart)) ? esc_html($labelModuleStart) : '';?></strong></label></label>
            <select id="moduleStart" class="form-control"></select>
        </div>
        <div class="form-group my-3">
            <label for="moduleGoal"><strong><?php echo (esc_html($labelModuleStart)) ? esc_html($labelModuleGoal) : '';?></strong></label></label>
            <select id="moduleGoal" class="form-control"></select>
        </div>
        <hr>                
        <div class="form-group my-3">
            <div class="col-md-12"><label for="showPriceReg"><?php echo (esc_html($labelRegPrice)) ? esc_html($labelRegPrice) : '';?></label></div>
            <div class="col-md-12"><input class="form-control" id="showPriceReg" type="text" value="" readonly /></div>
        </div>
        <div class="form-group my-3">
            <div class="col-md-12"><label for="showDiscount"><?php echo (esc_html($labelDiscount)) ? esc_html($labelDiscount) : '';?></label></div>
            <div class="col-md-12"><input class="form-control" id="showDiscount" type="text" value="" readonly /></div>
        </div>
        <hr>
        <div class="form-group my-3 priceall">
            <div class="col-md-12"><label for="showPriceRegAll"><strong><?php echo (esc_html($labelResult)) ? esc_html($labelResult) : '';?></strong></label></div>
            <div class="col-md-12"><input class="form-control" id="showPriceAll" type="text" value="" readonly /></div>
        </div>
    </form>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const moduleStart = document.getElementById("moduleStart");
            const moduleGoal = document.getElementById("moduleGoal");

            if (acfCourseData.moduleDataStart) {
                acfCourseData.moduleDataStart.forEach(module => {
                    const option = document.createElement("option");
                    option.value = module.value;
                    option.textContent = module.name;
                    moduleStart.appendChild(option);
                });
            }

            if (acfCourseData.moduleDataGoal) {
                acfCourseData.moduleDataGoal.forEach(module => {
                    const option = document.createElement("option");
                    option.value = module.value;
                    option.textContent = module.name;
                    moduleGoal.appendChild(option);
                });
            }
        });
    </script>
    <?php 
    return ob_get_clean();
}

add_shortcode('acf_course_calculator', 'acf_course_calculator');
?>
