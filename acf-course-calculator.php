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

    // ACF-Felder abrufen (angenommen, die Felder sind auf der aktuellen Seite vorhanden)
    // Headline
    $headline = get_field('calculator_headline','option');
    // Check modules and prices

    if( have_rows('kursmodule-einzeln','option') ):
        $modulesPrice = array();
        // Loop through rows.
        while( have_rows('kursmodule-einzeln','option') ) : the_row();
            // Load sub field value.
            //Label
            $labelModule = get_field('label-module','option');
            // options
            $name = get_sub_field('name','option');
            $preis = get_sub_field('preis','option');
            $modulesPrice[] = array(
                "name"  => $name,
                "preis" => $preis,
            );
        // End loop.
        endwhile;
        $jsonDataModules = json_encode($modulesPrice);
    endif;
    // Check Duration
    $labelDuration = esc_html(get_field('label-duration','option'));
    $unitSingle = esc_html(get_field('unit-single','option'));
    $unitMultiple = esc_html(get_field('unit-multiple','option'));
    $min = esc_html(get_field('minimale_dauer','option'));
    $max = esc_html(get_field('maximale_dauer','option'));

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
        
        // Über wp_localize_script() sichere JavaScript-Daten einfügen
        wp_localize_script('calculator-script', 'moduleData', $modulesPrice);
        wp_localize_script('calculator-script', 'discountData', $listDiscount);
    endif;

    // other Labels
    $labelLevel = esc_html(get_field('label-level','option'));
    $labelRegPrice = esc_html(get_field('label-reg-price','option'));
    $labelDiscount = esc_html(get_field('label-dicount','option'));
    $labelResult = esc_html(get_field('label-result','option'));
    $labelAverage = esc_html(get_field('label-average','option'));
        
    // Der PHP-Output (HTML) für das Template
    ob_start(); ?>

<form id="courseCalculator">
    <div class="form-group row flex-column modules">
    <?php if($headline) : ?>
        <div class="col-md-12"><legend><?php echo esc_html($headline);?></legend></div>
    <?php endif; ?>
        <div class="col-md-12"><label for="modulSelection"><strong><?php echo (esc_html($labelModule)) ? esc_html($labelModule) : '';?></strong></label></div>
        <div class="col-md-12 select">
            <select class="form-control" id="modulSelection" name="form-select"></select>
        </div>
    </div>
    <?php if( $min ) :  ?>
        <div class="form-group row flex-column duration">
            <div class="col-md-12">
                <label for="kursDauer" class="form-label"><strong><?php echo (esc_html($labelDuration)) ? esc_html($labelDuration) : '';?></strong></label>
            </div>
            <div class="col-md-12 month">
                <select class="form-control" id="modulDuration" name="duration">
                <?php for ($i = $min; $i <= $max; $i++) : ?>
                    <option value="<?php echo $i;?>"><?php echo($i > 1) ?  $i . '&nbsp;' . $unitMultiple : $i . '&nbsp;' . $unitSingle;?></option>';
                    <?php endfor; ?>
                </select>
            </div>
        </div>
    <?php endif;?>
    <hr>                
    <div class="form-group row">
        <div class="col-md-6"><label for="showLevel"><?php echo (esc_html($labelLevel)) ? esc_html($labelLevel) : '';?></label></div>
        <div class="col-md-6"><input  class="form-control" id="showLevel" type="text" value="" readonly /></div>
    </div>
    <div class="form-group row">
        <div class="col-md-6"><label for="showPriceReg"><?php echo (esc_html($labelRegPrice)) ? esc_html($labelRegPrice) : '';?></label></div>
        <div class="col-md-6"><input class="form-control" id="showPriceReg" type="text" value="" readonly /></div>
    </div>
    <div class="form-group row">
        <div class="col-md-6"><label for="showDiscount"><?php echo (esc_html($labelDiscount)) ? esc_html($labelDiscount) : '';?></label></div>
        <div class="col-md-6"><input class="form-control" id="showDiscount" type="text" value="" readonly /></div>
    </div>
    <hr>
    <div class="form-group row priceall">
        <div class="col-md-6"><label for="showPriceRegAll"><strong><?php echo (esc_html($labelResult)) ? esc_html($labelResult) : '';?></strong></label></div>
        <div class="col-md-6"><input class="form-control" id="showPriceAll" type="text" value="" readonly /></div>
    </div>
    <div class="form-group row">
        <div class="col-md-6"><label for="showPriceRegAll"><?php echo (esc_html($labelAverage)) ? esc_html($labelAverage) : '';?></label></div>
        <div class="col-md-6">
            <input class="form-control" id="showPriceRegAll" type="text" value="" readonly /></div>
    </div>
</form>
<script>
document.addEventListener("DOMContentLoaded",function(){const o=document.getElementById("modulSelection"),d=document.getElementById("modulDuration"),l=document.getElementById("showLevel"),u=document.getElementById("showDiscount"),s=document.getElementById("showPriceReg"),i=document.getElementById("showPriceAll"),c=document.getElementById("showPriceRegAll");function e(){var e=o.options[o.selectedIndex].dataset.index,t=parseInt(d.value),n=moduleData.length-e;let a=parseInt(e)+t-1;a>=moduleData.length&&(a=moduleData.length-1),l.value=moduleData[a].name;for(let e=0;e<d.options.length;e++)e+1>n?d.options[e].disabled=!0:d.options[e].disabled=!1}function t(){const t=parseInt(d.value);var e=discountData.find(e=>e.nr==t);u.value=e?e.discount+" EUR":"0 EUR"}function n(){var t=parseInt(o.options[o.selectedIndex].dataset.index),n=parseInt(d.value);let a=0;for(let e=0;e<n;e++)t+e<moduleData.length&&(a+=parseInt(moduleData[t+e].preis));s.value=a+" EUR";var e=parseInt(u.value),e=a-e,e=(i.value=e+" EUR",e/n);c.value=e.toFixed(2)+" EUR"}moduleData.forEach((e,t)=>{var n=document.createElement("option");n.value=e.preis,n.dataset.name=e.name,n.dataset.index=t,n.text=e.name,o.appendChild(n)}),o.addEventListener("change",function(){d.value="1";for(let e=0;e<d.options.length;e++)d.options[e].disabled=!1;e(),t(),n()}),d.addEventListener("change",function(){e(),t(),n()}),e(),t(),n()});
    </script>

    <?php
    return ob_get_clean();
}

// Shortcode registrieren
add_shortcode('acf_course_calculator', 'acf_course_calculator');
?>