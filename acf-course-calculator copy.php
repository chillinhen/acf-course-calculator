<?php
/*
Plugin Name: ACF Course Calculator
Description: Individuelles Plugin zur Berechnung der Kurskosten und Rabatte, einzubinden auf Kurseiten
Version: 1.0
Author: 
*/
function acf_course_calculator() {
    // Überprüfen, ob ACF verfügbar ist
    if (!function_exists('get_field')) {
        return '<p>Bitte installiere und aktiviere das ACF-Plugin.</p>';
    }
    // CSS und JS registrieren und einbinden
    wp_enqueue_style('calculator-styles', plugin_dir_url(__FILE__) . 'assets/calculator-styles.css');
    wp_enqueue_script('calculator-script', plugin_dir_url(__FILE__) . 'assets/calculator-script.js', array(), null, true);

   // Headline
   $headline = get_field('calculator_headline','option');

   // Check modules start and prices
   $labelModuleStart = get_field('label-module-start','option');
   if( have_rows('course-niveau-start','option') ):
        $modulesStart = array();
        // Loop through rows.
        while( have_rows('course-niveau-start','option') ) : the_row();
            // Load sub field value.
            // options
            $name = get_sub_field('name','option');
            $value = get_sub_field('preis','option');
            $modulesStart[] = array(
                "name"  => $name,
                "value" => $value,
            );
        // End loop.
        endwhile;
        $jsonDataModules = json_encode($modulesStart);
    endif;

    // Check modules goal and duration
   $labelModuleGoal = get_field('label-module-goal','option');
   if( have_rows('course-niveau-goal','option') ):
        $modulesGoal = array();
        // Loop through rows.
        while( have_rows('course-niveau-goal','option') ) : the_row();
            // Load sub field value.
            // options
            $name = get_sub_field('name','option');
            $value = get_sub_field('duration','option');
            $modulesGoal[] = array(
                "name"  => $name,
                "value" => $value,
            );
        // End loop.
        endwhile;
        $jsonDataModules = json_encode($modulesGoal);
    endif;

    // Check Discount
    if( have_rows('rabatte','option') ): 
        $listDiscount = array();
        while( have_rows('rabatte','option') ) : the_row(); 
            // Load sub field value.
            $price = get_sub_field('rabatt-einzeln','option');
            $month = get_sub_field('monat','option'); 
            $listDiscount[] = array(
                "nr"  => $month,
                "discount" => $price,
            );
        endwhile;
        $jsonDataDiscount = json_encode($listDiscount);
    endif;


    // Über wp_localize_script() sichere JavaScript-Daten einfügen
    $data = array(
        'moduleDataStart' => $modulesStart,
        'moduleDataGoal'  => $modulesGoal,
        'discountData'    => $listDiscount
        // Füge hier weitere Arrays hinzu:
        // 'moduleDataAnother' => $modulesAnother,
    );
    wp_localize_script('calculator-script', 'acfCourseData', $data);

    // other Labels
    $labelLevel = esc_html(get_field('label-level','option'));
    $labelRegPrice = esc_html(get_field('label-reg-price','option'));
    $labelDiscount = esc_html(get_field('label-dicount','option'));
    $labelResult = esc_html(get_field('label-result','option'));
    $labelAverage = esc_html(get_field('label-average','option'));
   
   
    // Der PHP-Output (HTML) für das Template
   ob_start(); ?>
   <form id="courseCalculator">
        <div class="form-group flex-column modules">
            <?php if($headline) : ?>
                <div class="col-md-12"><legend><?php echo esc_html($headline);?></legend></div>
            <?php endif; ?>
            <div class="col-md-12"><label for="moduleStart"><strong><?php echo (esc_html($labelModuleStart)) ? esc_html($labelModuleStart) : '';?></strong></label></div>
            <div class="col-md-12 select">
                <select class="form-control" id="moduleStart" name="form-select"></select>
            </div>
            <div class="col-md-12"><label for="moduleGoal"><strong><?php echo (esc_html($labelModuleGoal)) ? esc_html($labelModuleGoal) : '';?></strong></label></div>
            <div class="col-md-12 select">
                <select class="form-control" id="moduleGoal" name="form-select">
                    <?php foreach ($modulesGoal as $module): ?>
                        <option value="<?php echo esc_attr($module['value']); ?>">
                            <?php echo esc_html($module['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <hr>                
        <div class="form-group">
            <div class="col-md-12"><label for="showPriceReg"><?php echo (esc_html($labelRegPrice)) ? esc_html($labelRegPrice) : '';?></label></div>
            <div class="col-md-12"><input class="form-control" id="showPriceReg" type="text" value="" readonly /></div>
        </div>
        <div class="form-group">
            <div class="col-md-12"><label for="showDiscount"><?php echo (esc_html($labelDiscount)) ? esc_html($labelDiscount) : '';?></label></div>
            <div class="col-md-12"><input class="form-control" id="showDiscount" type="text" value="" readonly /></div>
        </div>
        <hr>
        <div class="form-group priceall">
            <div class="col-md-12"><label for="showPriceRegAll"><strong><?php echo (esc_html($labelResult)) ? esc_html($labelResult) : '';?></strong></label></div>
            <div class="col-md-12"><input class="form-control" id="showPriceAll" type="text" value="" readonly /></div>
        </div>
   </form>
   <script>
    document.addEventListener("DOMContentLoaded", function() {
        const moduleStart = document.getElementById("moduleStart");
        const moduleGoal = document.getElementById("moduleGoal");
        const showPriceReg = document.getElementById("showPriceReg");

        
        
    });
   </script>
    <?php 
    return ob_get_clean();
}

// Shortcode registrieren
add_shortcode('acf_course_calculator', 'acf_course_calculator');
?>