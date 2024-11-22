document.addEventListener("DOMContentLoaded", function() {
    console.log("Calculator JS loaded!");
     // Referenzen auf HTML-Elemente
     const moduleStart = document.getElementById("moduleStart");
     const moduleGoal = document.getElementById("moduleGoal");
     // const showLevel = document.getElementById("showLevel");
     // const showDiscount = document.getElementById("showDiscount");
     // const showPriceReg = document.getElementById("showPriceReg");
     // const showPriceAll = document.getElementById("showPriceAll");
     // const showPriceRegAll = document.getElementById("showPriceRegAll");
     
     const fillSelections = moduleDataArray => {
         moduleDataArray.foreach((module, index) => {
             console.log('fill');
             // let option = document.createElement("option");
             // option.value = module.value;
             // option.dataset.name = module.name;
             // option.dataset.index = index; // Index für spätere Verwendung
             // option.text = module.name;
             // modulSelection.appendChild(option);
         });
     }
     fillSelections(moduleStart);
     fillSelections(moduleGoal);
});