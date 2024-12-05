document.addEventListener("DOMContentLoaded", () => {
    const formElements = {
        moduleStart: document.getElementById("moduleStart"),
        moduleGoal: document.getElementById("moduleGoal"),
        countCourses: document.getElementById("countCourses"),
        showPriceReg: document.getElementById("showPriceReg"),
        showDiscount: document.getElementById("showDiscount"),
        showPriceAll: document.getElementById("showPriceAll"),
        rowDiscount: document.getElementById("rowDiscount"),
        labelResult: document.getElementById("labelResult"),
        labelDiscountResult: document.getElementById("labelDiscountResult"),
    };

    const createOptionsFromObj = (arr, sel, excludeFirst = false) => {
        sel.innerHTML = '';
        if (arr) {
            arr.forEach((module, index) => {
                // Überspringen, falls excludeFirst aktiviert ist und es sich um die erste Option handelt
                if (excludeFirst && index === 0) return;
    
                const option = document.createElement("option");
                option.value = module.value; // Preis als Wert speichern
                option.textContent = module.name;
                sel.appendChild(option);
            });
        }
    };

    const calculateDiscount = (courseCount) => {
        const discounts = acfCourseData.discountData
            .filter(d => courseCount >= d.nr)
            .map(d => parseFloat(d.discount));
        return discounts.length > 0 ? Math.max(...discounts) : 0;
    };

    const calculatePrices = () => {
        const startIndex = moduleStart.selectedIndex;
        const goalIndex = moduleGoal.selectedIndex + 1; // +1, um den ersten Kurs einzuschließen
    
        console.log("Startindex:", startIndex, "Zielindex:", goalIndex);
    
        // Sicherstellen, dass die Auswahl valide ist
        if (startIndex !== -1 && goalIndex > startIndex) {
            // Berechnung der Preise zwischen Start- und Zielmodul
            const pricesInBetween = acfCourseData.moduleDataCourses
                .slice(startIndex, goalIndex)
                .map(module => parseFloat(module.value));
    
            const courseCount = pricesInBetween.length; // Tatsächliche Kursanzahl
            const totalPrice = pricesInBetween.reduce((sum, price) => sum + price, 0);
            const discount = calculateDiscount(courseCount); // Rabatt basierend auf der Kursanzahl
            const finalPrice = totalPrice - discount;
    
            console.log("Preise zwischen Auswahl:", pricesInBetween);
            console.log("Gesamtsumme ohne Rabatt:", totalPrice);
            console.log("Anzahl der gebuchten Kurse:", courseCount);
            console.log("Rabatt:", discount);
            console.log("Gesamtpreis:", finalPrice);
    
            // Ergebnisse in die Felder schreiben
            countCourses.value = courseCount;
            showPriceReg.value = totalPrice.toLocaleString("de-DE", { minimumFractionDigits: 2 });
            showPriceAll.value = finalPrice.toLocaleString("de-DE", { minimumFractionDigits: 2 });
    
            // Rabatt-Anzeige
            if (discount > 0) {
                rowDiscount.style.display = "flex";
                showDiscount.value = discount.toLocaleString("de-DE", { minimumFractionDigits: 2 });
                labelResult.classList.add('d-none');
                labelDiscountResult.classList.remove('d-none');
            } else {
                rowDiscount.style.display = "none";
                labelResult.classList.remove('d-none');
                labelDiscountResult.classList.add('d-none');
            }
        } else {
            console.warn("Ungültige Auswahl für Start- oder Zielmodul.");
            countCourses.value = "";
            showPriceReg.value = "";
            showDiscount.value = "";
            showPriceAll.value = "";
        }
    };
    
    

    // Initial Dropdowns mit Optionen befüllen
    createOptionsFromObj(acfCourseData.moduleDataCourses, moduleStart);
    createOptionsFromObj(acfCourseData.moduleDataCourses, moduleGoal, true);
    calculatePrices();

    formElements.moduleStart.addEventListener("change", () => {
        const startIndex = formElements.moduleStart.selectedIndex;
        const filteredOptions = acfCourseData.moduleDataCourses.slice(startIndex + 1);
        createOptionsFromObj(filteredOptions, formElements.moduleGoal);
        formElements.moduleGoal.selectedIndex = 0;
        calculatePrices();
    });
    formElements.moduleGoal.addEventListener("change", calculatePrices);
});
