document.addEventListener("DOMContentLoaded", () => {
    const formElements = {
        moduleStart: document.getElementById("moduleStart"),
        moduleGoal: document.getElementById("moduleGoal"),
        countCourses: document.getElementById("countCourses"),
        rowCount: document.getElementById("rowCount"),
        showPriceReg: document.getElementById("showPriceReg"),
        rowPriceReg: document.getElementById("rowPriceReg"),
        showDiscount: document.getElementById("showDiscount"),
        showPriceAll: document.getElementById("showPriceAll"),
        rowDiscount: document.getElementById("rowDiscount"),
        labelResult: document.getElementById("labelResult"),
        labelDiscountResult: document.getElementById("labelDiscountResult"),
    };

    const modules = acfCourseData.moduleDataCourses || [];
    console.log(modules);
    const discounts = acfCourseData.listDiscount || [];

    // Standardoption für Dropdown hinzufügen
    const addPlaceholderOption = (select) => {
        const placeholderOption = document.createElement("option");
        placeholderOption.value = "";
        placeholderOption.textContent = "Bitte wählen";
        placeholderOption.disabled = true;
        placeholderOption.selected = true;
        select.appendChild(placeholderOption);
    };

    // Optionen in Dropdown einfügen
    const populateOptions = (arr, select, startIndex = 0) => {
        select.innerHTML = '';
        addPlaceholderOption(select); // "Bitte wählen"-Option
        arr.slice(startIndex).forEach((module, index) => {
            const option = document.createElement("option");
            option.value = module.value;
            option.dataset.index = startIndex + index;
            option.textContent = module.name;
            select.appendChild(option);
        });
    };

    // Preisberechnung und Rabattlogik
    const calculatePrice = () => {
        const startIndex = parseInt(formElements.moduleStart.selectedOptions[0]?.dataset.index || -1);
        const endIndex = parseInt(formElements.moduleGoal.selectedOptions[0]?.dataset.index || -1);

        if (startIndex < 0 || endIndex < 0 || startIndex >= endIndex) {
            resetResults();
            return;
        }

        let totalCourses = 0;
        let totalPrice = 0;

        // for (let i = startIndex; i < endIndex; i++) {
        //     totalCourses++;
        //     totalPrice += parseFloat(modules[i].value);
        //     console.log("Start Index:", startIndex, "End Index:", endIndex);
        // }
        totalPrice = 0; // Sicherstellen, dass totalPrice bei Null beginnt

for (let i = startIndex + 1; i <= endIndex; i++) {
    const previousValue = parseFloat(modules[i - 1]?.value || 0);
    const currentValue = parseFloat(modules[i]?.value || 0);

    if (isNaN(previousValue) || isNaN(currentValue)) {
        console.error(`Fehler bei Modul ${i}: Ungültiger Wert`);
        continue; // Fehlerhafte Module überspringen
    }

    totalPrice += currentValue; // Hinzufügen des aktuellen Modulkostenwerts
    console.log(
        `Von Kurs ${i - 1} (${previousValue} €) zu Kurs ${i} (${currentValue} €): Zwischensumme = ${totalPrice} €`
    );

    totalCourses++; // Kurszähler aktualisieren
}

        if (totalCourses > 0 && totalPrice > 0) {
            formElements.rowCount.classList.add('d-flex');
            formElements.rowPriceReg.classList.add('d-flex');
        } else {
            formElements.rowCount.classList.remove('d-flex');
            formElements.rowPriceReg.classList.remove('d-flex');
        }

        formElements.countCourses.value = totalCourses;
        formElements.showPriceReg.value = totalPrice.toLocaleString(undefined, { style: "currency", currency: "EUR" });

        // Rabattberechnung mit korrekter Formatierung
        //const discount = discounts.find(d => d.nr == totalCourses)?.discount || 0;
        const discountEntry = discounts.find(d => d.nr == totalCourses);
        const discount = parseFloat(discountEntry?.discount || 0);
        
        if(discount > 0) {
            formElements.showDiscount.value = discount.toLocaleString(undefined, { style: "currency", currency: "EUR" });
        } else {formElements.showDiscount.value = '';}
        // formElements.showDiscount.value = discount > 0 
        //     ? discount.toLocaleString(undefined, { style: "currency", currency: "EUR" }) 
        //     : ''; // Hier wird die Währungsformatierung korrekt angewendet

        if (discount > 0) {
            formElements.rowDiscount.setAttribute("aria-hidden", "false");
            formElements.rowDiscount.classList.add('d-flex');
        } else {
            formElements.rowDiscount.setAttribute("aria-hidden", "true");
            formElements.rowDiscount.classList.remove('d-flex');
        }

        const finalPrice = totalPrice - discount;
        formElements.showPriceAll.value = finalPrice.toLocaleString(undefined, { style: "currency", currency: "EUR" });

        // Labels aktualisieren basierend auf Rabatt
        if (discount > 0) {
            formElements.labelResult.classList.add('d-none');
            formElements.labelDiscountResult.classList.remove('d-none');
        } else {
            formElements.labelResult.classList.remove('d-none');
            formElements.labelDiscountResult.classList.add('d-none');
        }
    };

    

    // Zielkurse basierend auf Startkurs aktualisieren
    const updateGoalOptions = () => {
        const startIndex = parseInt(formElements.moduleStart.selectedOptions[0]?.dataset.index || -1);
        if (startIndex >= 0) {
            populateOptions(modules, formElements.moduleGoal, startIndex + 1);
        } else {
            populateOptions(modules, formElements.moduleGoal); // Alle Optionen, wenn kein Startkurs gewählt
        }
        resetResults();
    };

    // Ergebnisse zurücksetzen
    const resetResults = () => {
        formElements.countCourses.value = '';
        formElements.showPriceReg.value = '';
        formElements.showDiscount.value = '';
        formElements.rowDiscount.setAttribute("aria-hidden", "true");
        formElements.showPriceAll.value = '';
        formElements.labelResult.classList.remove("d-none");
        formElements.labelDiscountResult.classList.add("d-none");
    };

    // Initiale Optionen laden
    populateOptions(modules, formElements.moduleStart);
    populateOptions(modules, formElements.moduleGoal);

    // Event-Listener
    formElements.moduleStart.addEventListener("change", updateGoalOptions);
    formElements.moduleGoal.addEventListener("change", calculatePrice);
});
