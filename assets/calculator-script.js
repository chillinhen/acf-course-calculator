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

    
    createOptionsFromObj(acfCourseData.moduleDataCourses, formElements.moduleStart);
    createOptionsFromObj(acfCourseData.moduleDataCourses, formElements.moduleGoal);

});
