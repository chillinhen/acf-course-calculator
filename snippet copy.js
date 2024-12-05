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

    const createOptionsFromObj = (arr, sel) => {
        sel.innerHTML = '';
        arr.forEach(module => {
            const option = document.createElement("option");
            option.value = module.value;
            option.textContent = module.name;
            sel.appendChild(option);
        });
    };

    const calculateDiscount = (courseCount) => {
        const discounts = acfCourseData.discountData
            .filter(d => courseCount >= d.nr)
            .map(d => parseFloat(d.discount));
        return discounts.length > 0 ? Math.max(...discounts) : 0;
    };

    const calculatePrices = () => {
        const startIndex = moduleStart.selectedIndex;
        const goalIndex = moduleGoal.selectedIndex;
    
        if (startIndex >= 0 && goalIndex >= 0) {
            const pricesInBetween = acfCourseData.moduleDataCourses
                .slice(startIndex, startIndex + goalIndex + 1)
                .map(module => parseFloat(module.value));
    
            const courseCount = pricesInBetween.length;
            const totalPrice = pricesInBetween.reduce((sum, price) => sum + price, 0);
            const discount = calculateDiscount(courseCount);
            const finalPrice = totalPrice - discount;
    
            formElements.countCourses.value = courseCount;
            // Ausgabe mit deutscher Formatierung
            formElements.showPriceReg.value = totalPrice.toLocaleString("de-DE", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            formElements.showPriceAll.value = finalPrice.toLocaleString("de-DE", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
            if (discount > 0) {
                formElements.rowDiscount.classList.remove("hidden");
                formElements.rowDiscount.setAttribute("aria-hidden", "false");
                formElements.showDiscount.value = discount.toFixed(2);
                formElements.labelResult.classList.add("d-none");
                formElements.labelDiscountResult.classList.remove("d-none");
            } else {
                formElements.rowDiscount.classList.add("hidden");
                formElements.rowDiscount.setAttribute("aria-hidden", "true");
                formElements.labelResult.classList.remove("d-none");
                formElements.labelDiscountResult.classList.add("d-none");
            }
        }
    };

    createOptionsFromObj(acfCourseData.moduleDataCourses, formElements.moduleStart);
    createOptionsFromObj(acfCourseData.moduleDataCourses, formElements.moduleGoal);
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
