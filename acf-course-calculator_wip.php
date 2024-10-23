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
    document.addEventListener("DOMContentLoaded", function() {
        // Referenzen auf HTML-Elemente
        const modulSelection = document.getElementById("modulSelection");
        const modulDuration = document.getElementById("modulDuration");
        const showLevel = document.getElementById("showLevel");
        const showDiscount = document.getElementById("showDiscount");
        const showPriceReg = document.getElementById("showPriceReg");
        const showPriceAll = document.getElementById("showPriceAll");
        const showPriceRegAll = document.getElementById("showPriceRegAll");

        // 1. Populate #modulSelection with options from moduleData
        moduleData.forEach((module, index) => {
            let option = document.createElement("option");
            option.value = module.preis;
            option.dataset.name = module.name;
            option.dataset.index = index; // Index für spätere Verwendung
            //option.text = module.name + " (" + module.preis + " EUR)";
            option.text = module.name;
            modulSelection.appendChild(option);
        });

        // Update #modulDuration and #showLevel based on selections
        function updateLevelAndDuration() {
            const selectedIndex = modulSelection.options[modulSelection.selectedIndex].dataset.index;
            const selectedDuration = parseInt(modulDuration.value);
            const maxModules = moduleData.length - selectedIndex;

            // Update the #showLevel input
            let endLevelIndex = parseInt(selectedIndex) + selectedDuration - 1;
            if (endLevelIndex >= moduleData.length) {
                endLevelIndex = moduleData.length - 1;
            }
            showLevel.value = moduleData[endLevelIndex].name;

            // Disable extra months in #modulDuration if the selection exceeds available modules
            for (let i = 0; i < modulDuration.options.length; i++) {
                if (i + 1 > maxModules) {
                    modulDuration.options[i].disabled = true;
                } else {
                    modulDuration.options[i].disabled = false;
                }
            }
        }
        function resetDuration() {
            modulDuration.value = "1";
                // Enable all options in the module duration select
                for (let i = 0; i < modulDuration.options.length; i++) {
                    modulDuration.options[i].disabled = false;
                }    
        }

        // 4. Update #showDiscount based on #modulDuration
        function updateDiscount() {
            const selectedDuration = parseInt(modulDuration.value);
            const discountEntry = discountData.find(entry => entry.nr == selectedDuration);
            if (discountEntry) {
                showDiscount.value = discountEntry.discount + " EUR";
            } else {
                showDiscount.value = "0 EUR";
            }
        }

        // 1. Calculate regular total price based on selected modules and duration
        function updateTotalPrice() {
            const selectedIndex = parseInt(modulSelection.options[modulSelection.selectedIndex].dataset.index);
            const selectedDuration = parseInt(modulDuration.value);
            let totalPrice = 0;

            // Iterate through the selected modules and sum up the prices
            for (let i = 0; i < selectedDuration; i++) {
                if (selectedIndex + i < moduleData.length) {
                    totalPrice += parseInt(moduleData[selectedIndex + i].preis);
                }
            }
            showPriceReg.value = totalPrice + " EUR";

            // 2. Subtract discount from regular total price
            const discount = parseInt(showDiscount.value);
            const discountedPrice = totalPrice - discount;
            showPriceAll.value = discountedPrice + " EUR";

            // 3. Calculate price per module
            const pricePerModule = discountedPrice / selectedDuration;
            showPriceRegAll.value = pricePerModule.toFixed(2) + " EUR";
        }

        // Event listeners for changes in selections
        modulSelection.addEventListener("change", function() {
            resetDuration();
            updateLevelAndDuration();
            updateDiscount();
            updateTotalPrice();
        });


        modulDuration.addEventListener("change", function() {
            updateLevelAndDuration();
            updateDiscount();
            updateTotalPrice();
        });

        // Initial update on page load
        updateLevelAndDuration();
        updateDiscount();
        updateTotalPrice();
    });


    </script>

    <?php
    return ob_get_clean();
}

// Shortcode registrieren
add_shortcode('acf_course_calculator', 'acf_course_calculator');
?>